<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Catalog\Helper;

class Output extends \Magento\App\Helper\AbstractHelper
{
    /**
     * Array of existing handlers
     *
     * @var array
     */
    protected $_handlers;

    /**
     * Template processor instance
     *
     * @var \Magento\Filter\Template
     */
    protected $_templateProcessor = null;

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Escaper
     */
    protected $_escaper;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Escaper $escaper
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Escaper $escaper
    ) {
        $this->_eavConfig = $eavConfig;
        $this->_catalogData = $catalogData;
        $this->_escaper = $escaper;
        parent::__construct($context);
    }

    protected function _getTemplateProcessor()
    {
        if (null === $this->_templateProcessor) {
            $this->_templateProcessor = $this->_catalogData->getPageTemplateProcessor();
        }

        return $this->_templateProcessor;
    }

    /**
     * Adding method handler
     *
     * @param   string $method
     * @param   object $handler
     * @return  \Magento\Catalog\Helper\Output
     */
    public function addHandler($method, $handler)
    {
        if (!is_object($handler)) {
            return $this;
        }
        $method = strtolower($method);

        if (!isset($this->_handlers[$method])) {
            $this->_handlers[$method] = array();
        }

        $this->_handlers[$method][] = $handler;
        return $this;
    }

    /**
     * Get all handlers for some method
     *
     * @param   string $method
     * @return  array
     */
    public function getHandlers($method)
    {
        $method = strtolower($method);
        return isset($this->_handlers[$method]) ? $this->_handlers[$method] : array();
    }

    /**
     * Process all method handlers
     *
     * @param   string $method
     * @param   mixed $result
     * @param   array $params
     * @return unknown
     */
    public function process($method, $result, $params)
    {
        foreach ($this->getHandlers($method) as $handler) {
            if (method_exists($handler, $method)) {
                $result = $handler->$method($this, $result, $params);
            }
        }
        return $result;
    }

    /**
     * Prepare product attribute html output
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @param   string $attributeHtml
     * @param   string $attributeName
     * @return  string
     */
    public function productAttribute($product, $attributeHtml, $attributeName)
    {
        $attribute = $this->_eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeName);
        if ($attribute && $attribute->getId() && ($attribute->getFrontendInput() != 'media_image')
            && (!$attribute->getIsHtmlAllowedOnFront() && !$attribute->getIsWysiwygEnabled())) {
                if ($attribute->getFrontendInput() != 'price') {
                    $attributeHtml = $this->_escaper->escapeHtml($attributeHtml);
                }
                if ($attribute->getFrontendInput() == 'textarea') {
                    $attributeHtml = nl2br($attributeHtml);
                }
        }
        if ($attribute->getIsHtmlAllowedOnFront() && $attribute->getIsWysiwygEnabled()) {
            if ($this->_catalogData->isUrlDirectivesParsingAllowed()) {
                $attributeHtml = $this->_getTemplateProcessor()->filter($attributeHtml);
            }
        }

        $attributeHtml = $this->process('productAttribute', $attributeHtml, array(
            'product'   => $product,
            'attribute' => $attributeName
        ));

        return $attributeHtml;
    }

    /**
     * Prepare category attribute html output
     *
     * @param   \Magento\Catalog\Model\Category $category
     * @param   string $attributeHtml
     * @param   string $attributeName
     * @return  string
     */
    public function categoryAttribute($category, $attributeHtml, $attributeName)
    {
        $attribute = $this->_eavConfig->getAttribute(\Magento\Catalog\Model\Category::ENTITY, $attributeName);

        if ($attribute && ($attribute->getFrontendInput() != 'image')
            && (!$attribute->getIsHtmlAllowedOnFront() && !$attribute->getIsWysiwygEnabled())) {
            $attributeHtml = $this->_escaper->escapeHtml($attributeHtml);
        }
        if ($attribute->getIsHtmlAllowedOnFront() && $attribute->getIsWysiwygEnabled()) {
            if ($this->_catalogData->isUrlDirectivesParsingAllowed()) {
                $attributeHtml = $this->_getTemplateProcessor()->filter($attributeHtml);
            }
        }
        $attributeHtml = $this->process('categoryAttribute', $attributeHtml, array(
            'category'  => $category,
            'attribute' => $attributeName
        ));
        return $attributeHtml;
    }
}
