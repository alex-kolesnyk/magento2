<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Catalog layer filter abstract
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Layer\Filter;

abstract class AbstractFilter extends \Magento\Core\Block\Template
{
    /**
     * Catalog Layer Filter Attribute model
     *
     * @var \Magento\Catalog\Model\Layer\Filter\Attribute
     */
    protected $_filter;

    /**
     * Filter Model Name
     *
     * @var string
     */
    protected $_filterModelName;

    /**
     * Whether to display product count for layer navigation items
     * @var bool
     */
    protected $_displayProductCount = null;

    /**
     * Initialize filter template
     *
     */

    protected $_template = 'Magento_Catalog::layer/filter.phtml';

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * Layer filter factory
     *
     * @var \Magento\Catalog\Model\Layer\Filter\Factory
     */
    protected $_layerFilterFactory;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Filter\Factory $layerFilterFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\Catalog\Model\Layer\Filter\Factory $layerFilterFactory,
        array $data = array()
    ) {
        $this->_catalogData = $catalogData;
        $this->_layerFilterFactory = $layerFilterFactory;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Initialize filter model object
     *
     * @return \Magento\Catalog\Block\Layer\Filter\AbstractFilter
     */
    public function init()
    {
        $this->_initFilter();
        return $this;
    }

    /**
     * Init filter model object
     *
     * @return \Magento\Catalog\Block\Layer\Filter\AbstractFilter
     * @throws \Magento\Core\Exception
     */
    protected function _initFilter()
    {
        if (!$this->_filterModelName) {
            throw new \Magento\Core\Exception(__('The filter model name must be declared.'));
        }
        $this->_filter = $this->_layerFilterFactory->create($this->_filterModelName);
        $this->_filter->setLayer($this->getLayer());
        $this->_prepareFilter();

        $this->_filter->apply($this->getRequest(), $this);
        return $this;
    }

    /**
     * Prepare filter process
     *
     * @return \Magento\Catalog\Block\Layer\Filter\AbstractFilter
     */
    protected function _prepareFilter()
    {
        return $this;
    }

    /**
     * Retrieve name of the filter block
     *
     * @return string
     */
    public function getName()
    {
        return $this->_filter->getName();
    }

    /**
     * Retrieve filter items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->_filter->getItems();
    }

    /**
     * Retrieve filter items count
     *
     * @return int
     */
    public function getItemsCount()
    {
        return $this->_filter->getItemsCount();
    }

    /**
     * Getter for $_displayProductCount
     * @return bool
     */
    public function shouldDisplayProductCount()
    {
        if ($this->_displayProductCount === null) {
            $this->_displayProductCount = $this->_catalogData->shouldDisplayProductCountOnLayer();
        }
        return $this->_displayProductCount;
    }

    /**
     * Retrieve block html
     *
     * @return string
     */
    public function getHtml()
    {
        return parent::_toHtml();
    }
}
