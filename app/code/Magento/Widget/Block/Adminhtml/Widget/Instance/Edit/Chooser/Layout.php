<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Widget
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser;

/**
 * Widget Instance layouts chooser
 *
 * @method getArea()
 * @method getTheme()
 */
class Layout extends \Magento\Core\Block\Html\Select
{
    /**
     * @var \Magento\Core\Model\Layout\PageType\Config
     */
    protected $_config;

    /**
     * @param \Magento\Core\Block\Context $context
     * @param \Magento\Core\Model\Layout\PageType\Config $config
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Block\Context $context,
        \Magento\Core\Model\Layout\PageType\Config $config,
        array $data = array()
    ) {
        $this->_config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Add necessary options
     *
     * @return \Magento\Core\Block\AbstractBlock
     */
    protected function _beforeToHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('', __('-- Please Select --'));
            $pageTypes = $this->_config->getPageTypes();
            $this->_addPageTypeOptions($pageTypes);
        }
        return parent::_beforeToHtml();
    }

    /**
     * Add page types information to the options
     *
     * @param array $pageTypes
     */
    protected function _addPageTypeOptions(array $pageTypes)
    {
        $label = array();
        // Sort list of page types by label
        foreach ($pageTypes as $key => $row) {
            $label[$key]  = $row['label'];
        }
        array_multisort($label, SORT_STRING, $pageTypes);

        foreach ($pageTypes as $pageTypeName => $pageTypeInfo) {
            $params = array();
            $this->addOption($pageTypeName, $pageTypeInfo['label'], $params);
        }
    }
}
