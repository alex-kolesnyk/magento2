<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Validator
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Validation configuration files handler
 */
class Magento_Validator_Config extends Magento_Config_XmlAbstract
{
    /**
     * Constraints types
     */
    const CONSTRAINT_TYPE_ENTITY = 'entity';
    const CONSTRAINT_TYPE_PROPERTY = 'property';

    /**
     * @var array
     */
    protected $_validatorBuilders = array();

    /**
     * Get absolute path to validation.xsd
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return __DIR__ . '/validation.xsd';
    }

    public function getData()
    {
        return $this->_data;
    }

    /**
     * Get validator builder instance
     *
     * @param $entityName
     * @param $groupName
     * @param array $builderConfig
     * @return Magento_Validator_Builder
     */
    public function getValidatorBuilder($entityName, $groupName, array $builderConfig = null)
    {
        $builderKey = $entityName . '/' . $groupName;
        if (!array_key_exists($builderKey, $this->_validatorBuilders)) {
            if (array_key_exists('builder', $this->_data[$entityName][$groupName])) {
                $builderClass = $this->_data[$entityName][$groupName]['builder'];
            } else {
                $builderClass =  'Magento_Validator_Builder';
            }
            $this->_validatorBuilders[$builderKey] =
                new $builderClass($this->_data[$entityName][$groupName]['constraints']);
        }
        if ($builderConfig) {
            $this->_validatorBuilders[$builderKey]->addConfigurations($builderConfig);
        }
        return $this->_validatorBuilders[$builderKey];
    }

    /**
     * Create validator based on entity and group.
     *
     * @param string $entityName
     * @param string $groupName
     * @param array $builderConfig
     * @throws InvalidArgumentException
     * @return Magento_Validator
     */
    public function createValidator($entityName, $groupName, array $builderConfig = null)
    {
        if (!isset($this->_data[$entityName])) {
            throw new InvalidArgumentException(sprintf('Unknown validation entity "%s"', $entityName));
        }

        if (!isset($this->_data[$entityName][$groupName])) {
            throw new InvalidArgumentException(sprintf('Unknown validation group "%s" in entity "%s"', $groupName,
                $entityName));
        }

        return $this
            ->getValidatorBuilder($entityName, $groupName, $builderConfig)
            ->createValidator();
    }

    /**
     * Extract configuration data from the DOM structure
     *
     * @param DOMDocument $dom
     * @return array
     */
    protected function _extractData(DOMDocument $dom)
    {
        $result = array();

        /** @var DOMElement $entity */
        foreach ($dom->getElementsByTagName('entity') as $entity) {
            $result[$entity->getAttribute('name')] = $this->_extractEntityGroupsConstraintsData($entity);
        }
        return $result;
    }

    /**
     * Extract constraints associated with entity group using rules
     *
     * @param DOMElement $entity
     * @return array
     */
    protected function _extractEntityGroupsConstraintsData(DOMElement $entity)
    {
        $result = array();
        $rulesConstraints = $this->_extractRulesConstraintsData($entity);

        /** @var DOMElement $group */
        foreach ($entity->getElementsByTagName('group') as $group) {
            $groupConstraints = array();

            /** @var DOMElement $use */
            foreach ($group->getElementsByTagName('use') as $use) {
                $ruleName = $use->getAttribute('rule');
                if (isset($rulesConstraints[$ruleName])) {
                    $groupConstraints = array_merge($groupConstraints, $rulesConstraints[$ruleName]);
                }
            }

            $result[$group->getAttribute('name')] = array(
                'constraints' => $groupConstraints
            );
            $autoLoader = Magento_Autoload::getInstance();
            if ($group->hasAttribute('builder') && $autoLoader->classExists($group->getAttribute('builder'))) {
                $result[$group->getAttribute('name')]['builder'] = (string)$group->getAttribute('builder');
            }
        }

        unset($groupConstraints);
        unset($rulesConstraints);

        return $result;
    }

    /**
     * Extract constraints associated with rules
     *
     * @param DOMElement $entity
     * @return array
     */
    protected function _extractRulesConstraintsData(DOMElement $entity)
    {
        $rules = array();
        /** @var DOMElement $rule */
        foreach ($entity->getElementsByTagName('rule') as $rule) {
            $ruleName = $rule->getAttribute('name');

            /** @var DOMElement $propertyConstraints */
            foreach ($rule->getElementsByTagName('property_constraints') as $propertyConstraints) {
                /** @var DOMElement $property */
                foreach ($propertyConstraints->getElementsByTagName('property') as $property) {
                    /** @var DOMElement $constraint */
                    foreach ($property->getElementsByTagName('constraint') as $constraint) {
                        $rules[$ruleName][] = array(
                            'alias' => $constraint->getAttribute('alias'),
                            'class' => $constraint->getAttribute('class'),
                            'options' => $this->_extractConstraintOptions($constraint),
                            'property' => $property->getAttribute('name'),
                            'type' => self::CONSTRAINT_TYPE_PROPERTY,
                        );
                    }
                }
            }

            /** @var DOMElement $entityConstraints */
            foreach ($rule->getElementsByTagName('entity_constraints') as $entityConstraints) {
                /** @var DOMElement $constraint */
                foreach ($entityConstraints->getElementsByTagName('constraint') as $constraint) {
                    $rules[$ruleName][] = array(
                        'alias' => $constraint->getAttribute('alias'),
                        'class' => $constraint->getAttribute('class'),
                        'options' => $this->_extractConstraintOptions($constraint),
                        'type' => self::CONSTRAINT_TYPE_ENTITY,
                    );
                }
            }
        }

        return $rules;
    }

    /**
     * Extract constraint options.
     *
     * @param DOMElement $constraint
     * @return array|null
     */
    protected function _extractConstraintOptions(DOMElement $constraint)
    {
        $options = null;

        if ($constraint->hasChildNodes()) {
            $options = array();

            /**
             * Read constructor arguments
             *
             * <constraint class="Constraint">
             *     <argument>
             *         <option name="minValue">123</option>
             *         <option name="maxValue">234</option>
             *     </argument>
             *     <argument>0</argument>
             *     <argument>
             *         <callback class="Class" method="method" />
             *     </argument>
             * </constraint>
             */
            $children = $this->_collectChildren($constraint);
            $arguments = $this->_readArguments($children);
            if ($arguments) {
                $options['arguments'] = $arguments;
            }

            /**
             * Read constraint configurator callback
             *
             * <constraint class="Constraint">
             *     <callback class="Mage_Customer_Helper_Data" method="configureValidator"/>
             * </constraint>
             */
            $callback = $this->_readCallback($children);
            if ($callback) {
                $options['callback'] = $callback;
            }

            /**
             * Read constraint method configuration
             */
            $methods = $constraint->getElementsByTagName('method');
            if ($methods->length > 0) {
                /** @var $method DOMElement */
                foreach ($methods as $method) {
                    $children = $this->_collectChildren($method);
                    $methodName = (string)$method->getAttribute('name');
                    $methodOptions = array(
                        'method' => $methodName
                    );

                    /**
                     * <constraint class="Constraint">
                     *     <method name="setMaxValue">
                     *         <argument>
                     *             <option name="minValue">123</option>
                     *             <option name="maxValue">234</option>
                     *         </argument>
                     *         <argument>0</argument>
                     *         <argument>
                     *             <callback class="Class" method="method" />
                     *         </argument>
                     *     </method>
                     * </constraint>
                     */
                    $arguments = $this->_readArguments($children);
                    if ($arguments) {
                        $methodOptions['arguments'] = $arguments;
                    }

                    if (!array_key_exists('methods', $options)) {
                        $options['methods'] = array();
                    }
                    $options['methods'][$methodName] = $methodOptions;
                }
            }
        }
        return $options;
    }

    /**
     * Get element children.
     *
     * @param DOMElement $element
     * @return array
     */
    protected function _collectChildren($element)
    {
        $children = array();
        /** @var $node DOMElement */
        foreach ($element->childNodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }
            $nodeName = strtolower($node->nodeName);
            if (!array_key_exists($nodeName, $children)) {
                $children[$nodeName] = array();
            }
            $children[$nodeName][] = $node;
        }
        return $children;
    }

    /**
     * Get arguments.
     *
     * @param array $children
     * @return array|null
     */
    protected function _readArguments($children)
    {
        if (array_key_exists('argument', $children)) {
            $arguments = array();
            /** @var $node DOMElement */
            foreach ($children['argument'] as $node) {
                $nodeChildren = $this->_collectChildren($node);
                $callback = $this->_readCallback($nodeChildren);
                $options = $this->_readOptions($nodeChildren);
                if ($callback) {
                    $arguments[] = $callback[0];
                } elseif ($options) {
                    $arguments[] = $options;
                } else {
                    $arguments[] = new Magento_Validator_Constraint_Option_Scalar((string)$node->textContent);
                }

            }
            return $arguments;
        }
        return null;
    }

    /**
     * Get callback rules.
     *
     * @param array $children
     * @return array|null
     */
    protected function _readCallback($children)
    {
        if (array_key_exists('callback', $children)) {
            $callbacks = array();
            /** @var $callbackData DOMElement */
            foreach ($children['callback'] as $callbackData) {
                $callbacks[] = new Magento_Validator_Constraint_Option_Callback(
                    (string)$callbackData->getAttribute('class'),
                    (string)$callbackData->getAttribute('method')
                );
            }
            return $callbacks;
        }
        return null;
    }

    /**
     * Get options array.
     *
     * @param array $children
     * @return array|null
     */
    protected function _readOptions($children)
    {
        if (array_key_exists('option', $children)) {
            $data = array();
            /** @var $option DOMElement */
            foreach ($children['option'] as $option) {
                if ($option->hasAttribute('name')) {
                    $data[(string)$option->getAttribute('name')] = (string)$option->textContent;
                } else {
                    $data[] = (string)$option->textContent;
                }
            }
            return new Magento_Validator_Constraint_Option_Scalar($data);
        }
        return null;
    }

    /**
     * Get initial XML of a valid document.
     *
     * @return string
     */
    protected function _getInitialXml()
    {
        return '<?xml version="1.0" encoding="UTF-8"?><validation></validation>';
    }

    /**
     * Define id attributes for entities
     *
     * @return array
     */
    protected function _getIdAttributes()
    {
        return array(
            '/validation/entity' => 'name',
            '/validation/entity/rules/rule' => 'name',
            '/validation/entity/rules/rule/entity_constraints/constraint' => 'class',
            '/validation/entity/rules/rule/property_constraints/property/constraint' => 'class',
            '/validation/entity/rules/rule/property_constraints/property' => 'name',
            '/validation/entity/groups/group' => 'name',
            '/validation/entity/groups/group/uses/use' => 'rule',
        );
    }
}
