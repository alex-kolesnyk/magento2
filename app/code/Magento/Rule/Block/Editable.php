<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Rule
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Rule\Block;

class Editable
    extends \Magento\View\Block\AbstractBlock
    implements \Magento\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * Filter manager
     *
     * @var \Magento\Filter\FilterManager
     */
    protected $filter;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\View\Block\Context $context
     * @param \Magento\Filter\FilterManager $filter
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\View\Block\Context $context,
        \Magento\Filter\FilterManager $filter,
        array $data = array()
    ) {
        $this->_coreData = $coreData;
        $this->filter = $filter;
        parent::__construct($context, $data);
    }

    /**
     * Render element
     *
     * @see \Magento\Data\Form\Element\Renderer\RendererInterface::render()
     * @param \Magento\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Data\Form\Element\AbstractElement $element)
    {
        $element->addClass('element-value-changer');
        $valueName = $element->getValueName();

        if ($valueName === '') {
            $valueName = '...';
        }

        if ($element->getShowAsText()) {
            $html = ' <input type="hidden" class="hidden" id="' . $element->getHtmlId()
                . '" name="' . $element->getName() . '" value="' . $element->getValue() . '"/> '
                . htmlspecialchars($valueName) . '&nbsp;';
        } else {
            $html = ' <span class="rule-param"'
                . ($element->getParamId() ? ' id="' . $element->getParamId() . '"' : '') . '>'
                . '<a href="javascript:void(0)" class="label">';

            if ($this->_translator->isAllowed()) {
                $html .= $this->escapeHtml($valueName);
            } else {
                $html .= $this->escapeHtml($this->filter->truncate($valueName, array('length' => 33, 'etc' => '...')));
            }

            $html .= '</a><span class="element"> ' . $element->getElementHtml();

            if ($element->getExplicitApply()) {
                $html .= ' <a href="javascript:void(0)" class="rule-param-apply"><img src="'
                    . $this->getViewFileUrl('images/rule_component_apply.gif') . '" class="v-middle" alt="'
                    . __('Apply') . '" title="' . __('Apply') . '" /></a> ';
            }

            $html .= '</span></span>&nbsp;';
        }

        return $html;
    }
}
