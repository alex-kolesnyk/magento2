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
 * Abstract config form element renderer
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Backend_Block_System_Config_Form_Field
    extends Mage_Backend_Block_Template
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Application
     *
     * @var Mage_Core_Model_App
     */
    protected $_application;

    /**
     * @param Mage_Backend_Block_Template_Context $context
     * @param Mage_Core_Model_App $application
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Block_Template_Context $context,
        Mage_Core_Model_App $application,
        array $data = array()
    ) {
        $this->_application = $application;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve element HTML markup
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $element->getElementHtml();
    }

    /**
     * Retrieve HTML markup for given form element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $isCheckboxRequired = $this->_isInheritCheckboxRequired($element);

        // Disable element if value is inherited from other scope. Flag has to be set before the value is rendered.
        if ($element->getInherit() == 1 && $isCheckboxRequired) {
            $element->setDisabled(true);
        }

        $html = '<td class="label"><label for="' . $element->getHtmlId() . '">'
            . $element->getLabel() . '</label></td>';
        $html .= $this->_renderValue($element);

        if ($isCheckboxRequired) {
            $html .= $this->_renderInheritCheckbox($element);
        }

        $html .= $this->_renderScopeLabel($element);
        $html .= $this->_renderHint($element);

        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * Render element value
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _renderValue(Varien_Data_Form_Element_Abstract $element)
    {
        if ($element->getTooltip()) {
            $html = '<td class="value with-tooltip">';
            $html .= $this->_getElementHtml($element);
            $html .= '<div class="tooltip"><span class="help"><span></span></span>';
            $html .= '<div class="tooltip-content">' . $element->getTooltip() . '</div></div>';
        } else {
            $html = '<td class="value">';
            $html .= $this->_getElementHtml($element);
        }
        if ($element->getComment()) {
            $html .= '<p class="note"><span>' . $element->getComment() . '</span></p>';
        }
        $html .= '</td>';
        return $html;
    }

    /**
     * Render inheritance checkbox (Use Default or Use Website)
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _renderInheritCheckbox(Varien_Data_Form_Element_Abstract $element)
    {
        $htmlId = $element->getHtmlId();
        $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());
        $checkedHtml = ($element->getInherit() == 1) ? 'checked="checked"' : '';

        $html = '<td class="use-default">';
        $html .= '<input id="' . $htmlId . '_inherit" name="' . $namePrefix . '[inherit]" type="checkbox" value="1"'
            . ' class="checkbox config-inherit" ' . $checkedHtml
            . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" /> ';
        $html .= '<label for="' . $htmlId . '_inherit" class="inherit">' . $this->_getInheritCheckboxLabel($element)
            . '</label>';
        $html .= '</td>';

        return $html;
    }

    /**
     * Check if inheritance checkbox has to be rendered
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return bool
     */
    protected function _isInheritCheckboxRequired(Varien_Data_Form_Element_Abstract $element)
    {
        return $element->getCanUseWebsiteValue() || $element->getCanUseDefaultValue();
    }

    /**
     * Retrieve label for the inheritance checkbox
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getInheritCheckboxLabel(Varien_Data_Form_Element_Abstract $element)
    {
        $checkboxLabel = $this->helper('Mage_Backend_Helper_Data')->__('Use Default');
        if ($element->getCanUseWebsiteValue()) {
            $checkboxLabel =  $this->helper('Mage_Backend_Helper_Data')->__('Use Website');
        }
        return $checkboxLabel;
    }

    /**
     * Render scope label
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _renderScopeLabel(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<td class="scope-label">';
        if ($element->getScope() && false == $this->_application->isSingleStoreMode()) {
            $html .= $element->getScopeLabel();
        }
        $html .= '</td>';
        return $html;
    }

    /**
     * Render field hint
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _renderHint(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<td class="">';
        if ($element->getHint()) {
            $html .= '<div class="hint"><div style="display: none;">' . $element->getHint() . '</div></div>';
        }
        $html .= '</td>';
        return $html;
    }

    /**
     * Decorate field row html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @param string $html
     * @return string
     */
    protected function _decorateRowHtml($element, $html)
    {
        return '<tr id="row_' . $element->getHtmlId() . '">' . $html . '</tr>';
    }
}
