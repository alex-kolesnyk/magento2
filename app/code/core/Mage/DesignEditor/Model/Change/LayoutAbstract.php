<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Layout change model
 */
abstract class Mage_DesignEditor_Model_Change_LayoutAbstract extends Mage_DesignEditor_Model_ChangeAbstract
{
    /**
     * Layout change type identifier
     */
    const CHANGE_TYPE = 'layout';

    /**
     * Validate layout move change data passed to constructor
     *
     * @throws Mage_Core_Exception
     * @return Mage_DesignEditor_Model_Change_LayoutAbstract
     */
    protected function _validate()
    {
        $errors = array();
        foreach ($this->_getRequiredFields() as $field) {
            if ($this->getData($field) === null) {
                $errors[] = Mage::helper('Mage_DesignEditor_Helper_Data')->__('Invalid "%s" data', $field);
            }
        }

        if (count($errors)) {
            Mage::throwException(join("\n", $errors));
        }
        return $this;
    }

    /**
     * Get data to render layout update directive
     *
     * @abstract
     * @return array
     */
    abstract public function getLayoutUpdateData();

    /**
     * Get required data fields for layout change
     *
     * @return array
     */
    protected function _getRequiredFields()
    {
        return array('type', 'element_name', 'action_name');
    }

    /**
     * Get layout update directive for given layout change
     *
     * @return string
     */
    abstract public function getLayoutDirective();

    /**
     * Get attributes from XML layout update
     *
     * @param Varien_Simplexml_Element $layoutUpdate
     * @return array
     */
    protected function _getAttributes(Varien_Simplexml_Element $layoutUpdate)
    {
        $attributes = array();
        $attributes['type']        = $layoutUpdate->getAttribute('type') ?: 'layout';
        $attributes['action_name'] = $layoutUpdate->getName();
        foreach ($layoutUpdate->attributes() as $attributeName => $attributeValue) {
            $attributes[$attributeName] = (string) $attributeValue;
        }

        return $attributes;
    }
}
