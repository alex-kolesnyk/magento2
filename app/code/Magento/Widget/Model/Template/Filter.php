<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Widget
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Widget\Model\Template;

/**
 * Template Filter Model
 */
class Filter extends \Magento\Cms\Model\Template\Filter
{
    /**
     * @var \Magento\Widget\Model\Resource\Widget
     */
    protected $_widgetResource;

    /**
     * @var \Magento\Widget\Model\Widget
     */
    protected $_widget;

    /**
     * @param \Magento\Logger $logger
     * @param \Magento\Escaper $escaper
     * @param \Magento\Core\Model\View\Url $viewUrl
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\VariableFactory $coreVariableFactory
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\View\LayoutFactory $layoutFactory
     * @param \Magento\Widget\Model\Resource\Widget $widgetResource
     * @param \Magento\Widget\Model\Widget $widget
     */
    public function __construct(
        \Magento\Logger $logger,
        \Magento\Escaper $escaper,
        \Magento\Core\Model\View\Url $viewUrl,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\VariableFactory $coreVariableFactory,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\View\LayoutInterface $layout,
        \Magento\View\LayoutFactory $layoutFactory,
        \Magento\Widget\Model\Resource\Widget $widgetResource,
        \Magento\Widget\Model\Widget $widget
    ) {
        $this->_widgetResource = $widgetResource;
        $this->_widget = $widget;
        parent::__construct(
            $logger,
            $escaper,
            $viewUrl,
            $coreStoreConfig,
            $coreVariableFactory,
            $storeManager,
            $layout,
            $layoutFactory
        );
    }

    /**
     * Generate widget
     *
     * @param array $construction
     * @return string
     */
    public function widgetDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);

        // Determine what name block should have in layout
        $name = null;
        if (isset($params['name'])) {
            $name = $params['name'];
        }

        // validate required parameter type or id
        if (!empty($params['type'])) {
            $type = $params['type'];
        } elseif (!empty($params['id'])) {
            $preConfigured = $this->_widgetResource->loadPreconfiguredWidget($params['id']);
            $type = $preConfigured['widget_type'];
            $params = $preConfigured['parameters'];
        } else {
            return '';
        }

        // we have no other way to avoid fatal errors for type like 'cms/widget__link', '_cms/widget_link' etc.
        $xml = $this->_widget->getWidgetByClassType($type);
        if ($xml === null) {
            return '';
        }

        // define widget block and check the type is instance of Widget Interface
        $widget = $this->_layout->createBlock($type, $name, array('data' => $params));
        if (!$widget instanceof \Magento\Widget\Block\BlockInterface) {
            return '';
        }

        return $widget->toHtml();
    }
}
