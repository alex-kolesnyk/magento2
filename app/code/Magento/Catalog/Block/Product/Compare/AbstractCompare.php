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
 * Catalog Compare Products Abstract Block
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Product\Compare;

abstract class AbstractCompare extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * Catalog product compare
     *
     * @var \Magento\Catalog\Helper\Product\Compare
     */
    protected $_catalogProductCompare = null;

    /**
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\View\Block\Template\Context $context
     * @param \Magento\Math\Random $mathRandom
     * @param \Magento\Catalog\Helper\Product\Compare $catalogProductCompare
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Core\Helper\Data $coreData,
        \Magento\View\Block\Template\Context $context,
        \Magento\Math\Random $mathRandom,
        \Magento\Catalog\Helper\Product\Compare $catalogProductCompare,
        array $data = array()
    ) {
        $this->_catalogProductCompare = $catalogProductCompare;
        parent::__construct(
            $storeManager,
            $catalogConfig,
            $coreRegistry,
            $taxData,
            $catalogData,
            $coreData,
            $context,
            $mathRandom,
            $data
        );
    }

    /**
     * Retrieve Product Compare Helper
     *
     * @return \Magento\Catalog\Helper\Product\Compare
     */
    protected function _getHelper()
    {
        return $this->_catalogProductCompare;
    }

    /**
     * Retrieve Remove Item from Compare List URL
     *
     * @param \Magento\Catalog\Model\Product $item
     * @return string
     */
    public function getRemoveUrl($item)
    {
        return $this->_getHelper()->getRemoveUrl($item);
    }
}
