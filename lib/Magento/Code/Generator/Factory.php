<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Code
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Magento_Code_Generator_Factory extends Magento_Code_Generator_EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'factory';

    /**
     * Retrieve class properties
     *
     * @return array
     */
    protected function _getClassProperties()
    {
        $properties = parent::_getClassProperties();

        // protected $_instanceName = null;
        $properties[] = array(
            'name'       => '_instanceName',
            'visibility' => 'protected',
            'docblock'   => array(
                'shortDescription' => 'Instance name to create',
                'tags'             => array(
                    array('name' => 'var', 'description' => 'string')
                )
            ),
        );
        return $properties;
    }

    /**
     * Get default constructor definition for generated class
     *
     * @return array
     */
    protected function _getDefaultConstructorDefinition()
    {
        // public function __construct(\Magento_ObjectManager $objectManager, $instanceName = <DEFAULT_INSTANCE_NAME>)
        return array(
            'name'       => '__construct',
            'parameters' => array(
                array(
                    'name' => 'objectManager',
                    'type' => '\Magento_ObjectManager'
                ),
                array(
                    'name' => 'instanceName',
                    'defaultValue' => $this->_getSourceClassName(),
                ),
            ),
            'body' => "\$this->_objectManager = \$objectManager;\n\$this->_instanceName = \$instanceName;",
            'docblock' => array(
                'shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor',
                'tags'             => array(
                    array(
                        'name'        => 'param',
                        'description' => '\Magento_ObjectManager $objectManager'
                    ),
                    array(
                        'name'        => 'param',
                        'description' => 'string $instanceName'
                    ),
                ),
            ),
        );
    }

    /**
     * Returns list of methods for class generator
     *
     * @return array
     */
    protected function _getClassMethods()
    {
        $construct = $this->_getDefaultConstructorDefinition();

        // public function create(array $data = array())
        $create = array(
            'name'       => 'create',
            'parameters' => array(
                array('name' => 'data', 'type' => 'array', 'defaultValue' => array()),
            ),
            'body' => 'return $this->_objectManager->create($this->_instanceName, $data);',
            'docblock' => array(
                'shortDescription' => 'Create class instance with specified parameters',
                'tags'             => array(
                    array(
                        'name'        => 'param',
                        'description' => 'array $data'
                    ),
                    array(
                        'name'        => 'return',
                        'description' => $this->_getFullyQualifiedClassName($this->_getSourceClassName())
                    ),
                ),
            ),
        );

        return array($construct, $create);
    }
}
