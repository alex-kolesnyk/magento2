<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Grid radiogroup column renderer
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Block_Widget_Grid_Column_Renderer_Radio
    extends Mage_Backend_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_defaultWidth = 55;
    protected $_values;

    /**
     * @var Mage_Backend_Block_Widget_Grid_Column_Renderer_Options_Converter
     */
    protected $_converter;

    /**
     * @param Mage_Backend_Block_Context $context
     * @param Mage_Backend_Block_Widget_Grid_Column_Renderer_Options_Converter $converter
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Block_Context $context,
        Mage_Backend_Block_Widget_Grid_Column_Renderer_Options_Converter $converter,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_converter = $converter;
    }

    /**
     * Prepare data for renderer
     *
     * @return array
     */
    protected function _getSimpleValue()
    {
        $values = $this->getColumn()->getValues();
        return $this->_converter->toFlatArray($values);
    }

    /**
     * Returns all values for the column
     *
     * @return array
     */
    public function getValues()
    {
        if (is_null($this->_values)) {
            $this->_values = $this->getColumn()->getData('values') ? $this->getColumn()->getData('values') : array();
        }
        return $this->_values;
    }
    /**
     * Renders grid column
     *
     * @param   Magento_Object $row
     * @return  string
     */
    public function render(Magento_Object $row)
    {
        $values = $this->_getSimpleValue();
        $value  = $row->getData($this->getColumn()->getIndex());
        if (is_array($values)) {
            $checked = in_array($value, $values) ? ' checked="checked"' : '';
        } else {
            $checked = ($value === $this->getColumn()->getValue()) ? ' checked="checked"' : '';
        }
        $html = '<input type="radio" name="' . $this->getColumn()->getHtmlName() . '" ';
        $html .= 'value="' . $row->getId() . '" class="radio"' . $checked . '/>';
        return $html;
    }
}
