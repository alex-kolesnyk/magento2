<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Product Stores tab
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Magento_Adminhtml_Block_Catalog_Product_Edit_Tab_Websites extends Magento_Backend_Block_Store_Switcher
{
    protected $_storeFromHtml;

    protected $_template = 'catalog/product/edit/websites.phtml';

    /**
     * Core registry
     *
     * @var Magento_Core_Model_Registry
     */
    protected $_coreRegistry = null;

    /**
     * Constructor
     *
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Backend_Block_Template_Context $context
     * @param Magento_Core_Model_App $application
     * @param Magento_Core_Model_Website_Factory $websiteFactory
     * @param Magento_Core_Model_Store_Group_Factory $storeGroupFactory
     * @param Magento_Core_Model_StoreFactory $storeFactory
     * @param Magento_Core_Model_Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Magento_Core_Helper_Data $coreData,
        Magento_Backend_Block_Template_Context $context,
        Magento_Core_Model_App $application,
        Magento_Core_Model_Website_Factory $websiteFactory,
        Magento_Core_Model_Store_Group_Factory $storeGroupFactory,
        Magento_Core_Model_StoreFactory $storeFactory,
        Magento_Core_Model_Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct(
            $coreData, $context, $application, $websiteFactory, $storeGroupFactory, $storeFactory, $data
        );
    }

    /**
     * Retrieve edited product model instance
     *
     * @return Magento_Catalog_Model_Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Get store ID of current product
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getProduct()->getStoreId();
    }

    /**
     * Get ID of current product
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->getProduct()->getId();
    }

    /**
     * Retrieve array of website IDs of current product
     *
     * @return array
     */
    public function getWebsites()
    {
        return $this->getProduct()->getWebsiteIds();
    }

    /**
     * Returns whether product associated with website with $websiteId
     *
     * @param int $websiteId
     * @return bool
     */
    public function hasWebsite($websiteId)
    {
        return in_array($websiteId, $this->getProduct()->getWebsiteIds());
    }

    /**
     * Check websites block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->getProduct()->getWebsitesReadonly();
    }

    /**
     * Retrieve store name by its ID
     *
     * @param int $storeId
     * @return null|string
     */
    public function getStoreName($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getName();
    }

    /**
     * Get HTML of store chooser
     *
     * @param Magento_Core_Model_Store $storeTo
     * @return string
     */
    public function getChooseFromStoreHtml($storeTo)
    {
        if (!$this->_storeFromHtml) {
            $this->_storeFromHtml = '<select name="copy_to_stores[__store_identifier__]" disabled="disabled">';
            $this->_storeFromHtml.= '<option value="0">'.__('Default Values').'</option>';
            foreach ($this->getWebsiteCollection() as $_website) {
                if (!$this->hasWebsite($_website->getId())) {
                    continue;
                }
                $optGroupLabel = $this->escapeHtml($_website->getName());
                $this->_storeFromHtml .= '<optgroup label="' . $optGroupLabel . '"></optgroup>';
                foreach ($this->getGroupCollection($_website) as $_group) {
                    $optGroupName = $this->escapeHtml($_group->getName());
                    $this->_storeFromHtml .= '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;' . $optGroupName . '">';
                    foreach ($this->getStoreCollection($_group) as $_store) {
                        $this->_storeFromHtml .= '<option value="' . $_store->getId() . '">&nbsp;&nbsp;&nbsp;&nbsp;';
                        $this->_storeFromHtml .= $this->escapeHtml($_store->getName()) . '</option>';
                    }
                }
                $this->_storeFromHtml .= '</optgroup>';
            }
            $this->_storeFromHtml .= '</select>';
        }
        return str_replace('__store_identifier__', $storeTo->getId(), $this->_storeFromHtml);
    }
}
