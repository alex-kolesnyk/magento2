<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Code
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Magento_Code_Generator_Proxy extends Magento_Code_Generator_EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'proxy';

    /**
     * @param string $modelClassName
     * @return string
     */
    protected function _getDefaultResultClassName($modelClassName)
    {
        return $modelClassName . '_' . ucfirst(static::ENTITY_TYPE);
    }

    /**
     * Retrieve class properties
     *
     * @return array
     */
    protected function _getClassProperties()
    {
        $properties = parent::_getClassProperties();
        $properties[] = array(
            'name'       => '_subject',
            'visibility' => 'protected',
            'docblock'   => array(
                'shortDescription' => 'Proxied instance',
                'tags'             => array(
                    array('name' => 'var', 'description' => $this->_getSourceClassName())
                )
            ),
        );
        return $properties;
    }

    /**
     * @return array
     */
    protected function _getClassMethods()
    {
        $construct = $this->_getDefaultConstructorDefinition();

        // create proxy methods for all non-static and non-final public methods (excluding constructor)
        $methods = array($construct);
        $methods[] = array(
            'name'     => '__sleep',
            'body'     => 'return array(\'_subject\');',
            'docblock' => array(
                'shortDescription' => '@return array',
            ),
        );
        $methods[] = array(
            'name'     => '__wakeup',
            'body'     => '$this->_objectManager = Mage::getObjectManager();',
            'docblock' => array(
                'shortDescription' => 'Retrieve ObjectManager from global scope',
            ),
        );
        $methods[] = array(
            'name'     => '__clone',
            'body'     => '$this->_subject = clone $this->_objectManager->get(self::CLASS_NAME);',
            'docblock' => array(
                'shortDescription' => 'Clone proxied instance',
            ),
        );
        $reflectionClass = new ReflectionClass($this->_getSourceClassName());
        $publicMethods   = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $method) {
            if (!($method->isConstructor() || $method->isFinal() || $method->isStatic() || $method->isDestructor())
                && !in_array($method->getName(), array('__sleep', '__wakeup', '__clone'))
            ) {
                $methods[] = $this->_getMethodInfo($method);
            }
        }

        return $methods;
    }

    /**
     * @return string
     */
    protected function _generateCode()
    {
        $this->_classGenerator->setExtendedClass($this->_getFullyQualifiedClassName($this->_getSourceClassName()));

        return parent::_generateCode();
    }

    /**
     * Collect method info
     *
     * @param ReflectionMethod $method
     * @return array
     */
    protected function _getMethodInfo(ReflectionMethod $method)
    {
        $parameterNames = array();
        $parameters     = array();
        foreach ($method->getParameters() as $parameter) {
            $parameterNames[] = '$' . $parameter->getName();
            $parameters[]     = $this->_getMethodParameterInfo($parameter);
        }

        $methodInfo = array(
            'name'       => $method->getName(),
            'parameters' => $parameters,
            'body'       => $this->_getMethodBody($method->getName(), $parameterNames),
            'docblock'   => array(
                'shortDescription' => '{@inheritdoc}',
            ),
        );

        return $methodInfo;
    }

    /**
     * Collect method parameter info
     *
     * @param ReflectionParameter $parameter
     * @return array
     */
    protected function _getMethodParameterInfo(ReflectionParameter $parameter)
    {
        $parameterInfo = array(
            'name'              => $parameter->getName(),
            'passedByReference' => $parameter->isPassedByReference()
        );

        if ($parameter->isArray()) {
            $parameterInfo['type'] = 'array';
        } elseif ($parameter->getClass()) {
            $parameterInfo['type'] = $this->_getFullyQualifiedClassName($parameter->getClass()->getName());
        }

        if ($parameter->isOptional() && $parameter->isDefaultValueAvailable()) {
            $defaultValue = $parameter->getDefaultValue();
            if (is_string($defaultValue)) {
                $parameterInfo['defaultValue'] = $this->_escapeDefaultValue($parameter->getDefaultValue());
            } elseif ($defaultValue === null) {
                $parameterInfo['defaultValue'] = $this->_getNullDefaultValue();
            } else {
                $parameterInfo['defaultValue'] = $defaultValue;
            }
        }

        return $parameterInfo;
    }

    /**
     * Build proxy method body
     *
     * @param string $name
     * @param array $parameters
     * @return string
     */
    protected function _getMethodBody($name, array $parameters = array())
    {
        if (count($parameters) == 0) {
            $methodCall = sprintf('%s()', $name);
        } else {
            $methodCall = sprintf('%s(%s)', $name, implode(', ', $parameters));
        }
        return "if (!\$this->_subject) {\n" .
            "    \$this->_subject = \$this->_objectManager->get(self::CLASS_NAME);\n" .
            "}\n".
            'return $this->_subject->' . $methodCall . ';';
    }
}
