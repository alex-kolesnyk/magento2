<?php

/**
 * Entity/Attribute/Model - attribute frontend abstract
 *
 * @package    Mage
 * @subpackage Mage_Eav
 * @author     Moshe Gurvich moshe@varien.com>
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 */
abstract class Mage_Eav_Model_Entity_Attribute_Frontend_Abstract implements Mage_Eav_Model_Entity_Attribute_Frontend_Interface
{
    /**
     * Reference to the attribute instance
     *
     * @var Mage_Eav_Model_Entity_Attribute_Abstract
     */
    protected $_attribute;
    
    /**
     * Set attribute instance
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @return Mage_Eav_Model_Entity_Attribute_Frontend_Abstract
     */
    public function setAttribute($attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }
    
    /**
     * Get attribute instance
     *
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function getAttribute()
    {
        return $this->_attribute;
    }
    
    /**
     * Get attribute type for user interface form
     *
     * @return string
     */
    public function getInputType()
    {
        return $this->getAttribute()->getFrontendInput();
    }
    
    public function getLabel()
    {
        $label = $this->getAttribute()->getFrontendLabel();
        if (empty($label)) {
            $label = $this->getAttribute()->getAttributeCode();
        }
        return $label;
    }
    
    public function getClass()
    {
        $out = $this->getAttribute()->getFrontendClass();
        if ($this->getAttribute()->getIsRequired()) {
            $out .= ' required-entry';
        }
        return $out;
    }
    
    public function getConfigField($fieldName)
    {
        return $this->getAttribute()->getData('frontend_'.$fieldName);
    }
    
    /**
     * Get select options in case it's select box and options source is defined
     *
     * @return array
     */
    public function getSelectOptions()
    {
        return $this->getAttribute()->getSource()->getAllOptions();
    }
}