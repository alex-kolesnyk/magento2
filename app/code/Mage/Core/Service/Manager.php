<?php

class Mage_Core_Service_Manager extends Varien_Object
{
    /** @var Magento_ObjectManager */
    protected $_objectManager;

    /** @var Mage_Core_Service_Idl */
    protected $_definition;

    /**  @var Varien_Object */
    protected $_idl = null;

    public function __construct(Magento_ObjectManager $objectManager, Mage_Core_Service_Idl $definition)
    {
        $this->_objectManager = $objectManager;
        $this->_definition    = $definition;
    }

    /**
     * Call service method
     *
     * @param string $serviceId
     * @param mixed $args
     * @return Mage_Core_Service_Args $args
     */
    public function call($serviceId, $method, $args = null)
    {
        $service = $this->getService($serviceId);

        $args = $this->extractArguments($serviceId, $args);

        $result  = $service->$method($args);

        return $result;
    }

    /**
     * Look up for service model
     *
     * @param string $serviceId
     * @return Mage_Core_Service_Abstract $service
     */
    public function getService($serviceId)
    {
        $service = $this->_objectManager->get($serviceId);

        return $service;
    }

    /**
     * Look up for a given service arguments in environment
     *
     * @param string $serviceId
     * @param mixed $args
     * @return Mage_Core_Service_Args $args
     */
    public function extractArguments($serviceId, $args)
    {
        if ($args instanceof Mage_Core_Service_Args) {
            return $args;
        }

        $requestParams = array();

        $scheme = $this->_definition->getElement($serviceId);
        $params = (array) Mage::app()->getRequest()->getParams($serviceId);

        if (null !== $args) {
            if (is_string($args) || is_numeric($args)) {
                $args = array('id' => $args);
            }
            // TODO: how about an object?
            $params = array_merge($params, $args);
        }

        if ($params) {
            $requestParams = $this->filter($params, $scheme);
        }

        $args = $this->_objectManager->get('Mage_Core_Service_Args');
        $args->setData($requestParams);

        return $args;
    }

    public function filter(array $params, array $scheme)
    {
        $resourceIdFieldAlias = $scheme['id_field_alias'];

        foreach ($params as $field => $value) {
            if ($resourceIdFieldAlias === $field) {
                continue;
            }
            if (!array_key_exists($field, $scheme['fields'])) {
                unset($params[$field]);
            }
        }

        if (!isset($params[$resourceIdFieldAlias])) {
            $params[$resourceIdFieldAlias] = Mage::app()->getRequest()->getParam($resourceIdFieldAlias);
        }

        foreach ($scheme['global_params'] as $field => $config) {
            if (!isset($params[$field])) {
                $params[$field] = Mage::app()->getRequest()->getParam($field, $config['default']);
            }
        }

        return $params;
    }
}
