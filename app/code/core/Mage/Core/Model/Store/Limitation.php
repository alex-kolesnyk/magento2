<?php
/**
 * Functional limitation for number of stores
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Core_Model_Store_Limitation
{
    /**
     * @var Mage_Core_Model_Resource_Store
     */
    private $_resource;

    /**
     * @var int
     */
    private $_allowedQty = 0;

    /**
     * @var bool
     */
    private $_isRestricted = false;

    /**
     * Determine restriction
     *
     * @param Mage_Core_Model_Resource_Store $resource
     * @param Mage_Core_Model_Config $config
     */
    public function __construct(Mage_Core_Model_Resource_Store $resource, Mage_Core_Model_Config $config)
    {
        $this->_resource = $resource;
        $allowedQty = (string)$config->getNode('global/functional_limitation/max_store_count');
        if ('' === $allowedQty) {
            return;
        }
        $this->_allowedQty = (int)$allowedQty;
        $this->_isRestricted = true;
    }

    /**
     * Whether restriction of creating new items is effective
     *
     * @return bool
     */
    public function isCreateRestricted()
    {
        if ($this->_isRestricted) {
            return $this->_resource->countAll() >= $this->_allowedQty;
        }
        return false;
    }

    /**
     * User notification message about the restriction
     *
     * @return string
     */
    public static function getCreateRestrictionMessage()
    {
        return Mage::helper('Mage_Core_Helper_Data')->__('You are using the maximum number of store views allowed.');
    }
}
