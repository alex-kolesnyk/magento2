<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Customer
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Customer abstract API resource
 *
 * @category   Magento
 * @package    Magento_Customer
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Magento_Customer_Model_Api_Resource extends Magento_Api_Model_Resource_Abstract
{
    /**
     * Default ignored attribute codes
     *
     * @var array
     */
    protected $_ignoredAttributeCodes = array('entity_id', 'attribute_set_id', 'entity_type_id');

    /**
     * Default ignored attribute types
     *
     * @var array
     */
    protected $_ignoredAttributeTypes = array();

    /**
     * Check is attribute allowed
     *
     * @param Magento_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param array $attributes
     * @return boolean
     */
    protected function _isAllowedAttribute($attribute, array $filter = null)
    {
        if (!is_null($filter)
            && !( in_array($attribute->getAttributeCode(), $filter)
                  || in_array($attribute->getAttributeId(), $filter))) {
            return false;
        }

        return !in_array($attribute->getFrontendInput(), $this->_ignoredAttributeTypes)
               && !in_array($attribute->getAttributeCode(), $this->_ignoredAttributeCodes);
    }

    /**
     * Return list of allowed attributes
     *
     * @param Magento_Eav_Model_Entity_Abstract $entity
     * @param array $filter
     * @return array
     */
    public function getAllowedAttributes($entity, array $filter = null)
    {
        $attributes = $entity->getResource()
                        ->loadAllAttributes($entity)
                        ->getAttributesByCode();
        $result = array();
        foreach ($attributes as $attribute) {
            if ($this->_isAllowedAttribute($attribute, $filter)) {
                $result[$attribute->getAttributeCode()] = $attribute;
            }
        }

        return $result;
    }
} // Class Magento_Customer_Model_Api_Resource End
