<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_TargetRule
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * TargetRule Catalog Product List Abstract Block
 *
 * @category   Enterprise
 * @package    Enterprise_TargetRule
 */
abstract class Enterprise_TargetRule_Block_Catalog_Product_List_Abstract
    extends Enterprise_TargetRule_Block_Product_Abstract
{
    /**
     * TargetRule Index instance
     *
     * @var Enterprise_TargetRule_Model_Index
     */
    protected $_index;

    /**
     * Array of exclude Product Ids
     *
     * @var array
     */
    protected $_excludeProductIds;

    /**
     * Array of all product ids in list
     *
     * @var null|array
     */
    protected $_allProductIds = null;

    /**
     * Retrieve current product instance (if actual and available)
     *
     * @return Magento_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('product');
    }

    /**
     * Retrieve Catalog Product List Type prefix
     * without last underscore
     *
     * @return string
     */
    protected function _getTypePrefix()
    {
        switch ($this->getProductListType()) {
            case Enterprise_TargetRule_Model_Rule::RELATED_PRODUCTS:
                $prefix = 'related';
                break;

            case Enterprise_TargetRule_Model_Rule::UP_SELLS:
                $prefix = 'upsell';
                break;

            default:
                Mage::throwException(
                    Mage::helper('Enterprise_TargetRule_Helper_Data')->__('Undefined Catalog Product List Type')
                );
        }
        return $prefix;
    }

    /**
     * Retrieve Target Rule Index instance
     *
     * @return Enterprise_TargetRule_Model_Index
     */
    protected function _getTargetRuleIndex()
    {
        if (is_null($this->_index)) {
            $this->_index = Mage::getModel('Enterprise_TargetRule_Model_Index');
        }
        return $this->_index;
    }

    /**
     * Retrieve position limit product attribute name
     *
     * @return string
     */
    protected function _getPositionLimitField()
    {
        return sprintf('%s_tgtr_position_limit', $this->_getTypePrefix());
    }

    /**
     * Retrieve position behavior product attribute name
     *
     * @return string
     */
    protected function _getPositionBehaviorField()
    {
        return sprintf('%s_tgtr_position_behavior', $this->_getTypePrefix());
    }

    /**
     * Retrieve Maximum Number Of Product
     *
     * @return int
     */
    public function getPositionLimit()
    {
        $limit = $this->getProduct()->getData($this->_getPositionLimitField());
        if (is_null($limit)) { // use configuration settings
            $limit = $this->getTargetRuleHelper()->getMaximumNumberOfProduct($this->getProductListType());
            $this->getProduct()->setData($this->_getPositionLimitField(), $limit);
        }
        return $this->getTargetRuleHelper()->getMaxProductsListResult($limit);
    }

    /**
     * Retrieve Position Behavior
     *
     * @return int
     */
    public function getPositionBehavior()
    {
        $behavior = $this->getProduct()->getData($this->_getPositionBehaviorField());
        if (is_null($behavior)) { // use configuration settings
            $behavior = $this->getTargetRuleHelper()->getShowProducts($this->getProductListType());
            $this->getProduct()->setData($this->_getPositionBehaviorField(), $behavior);
        }
        return $behavior;
    }

    /**
     * Retrieve array of exclude product ids
     *
     * @return array
     */
    public function getExcludeProductIds()
    {
        if (is_null($this->_excludeProductIds)) {
            $this->_excludeProductIds = array($this->getProduct()->getEntityId());
        }
        return $this->_excludeProductIds;
    }

    /**
     * Get link collection with limit parameter
     *
     * @throws Magento_Core_Exception
     * @param null|int $limit
     * @return Magento_Catalog_Model_Resource_Product_Link_Product_Collection|null
     */
    protected function _getPreparedTargetLinkCollection($limit = null)
    {
        $linkCollection = null;
        switch ($this->getProductListType()) {
            case Enterprise_TargetRule_Model_Rule::RELATED_PRODUCTS:
                $linkCollection = $this->getProduct()
                    ->getRelatedProductCollection();
                break;

            case Enterprise_TargetRule_Model_Rule::UP_SELLS:
                $linkCollection = $this->getProduct()
                    ->getUpSellProductCollection();
                break;

            default:
                Mage::throwException(
                    Mage::helper('Enterprise_TargetRule_Helper_Data')->__('Undefined Catalog Product List Type')
                );
        }

        if (!is_null($limit)) {
            $this->_addProductAttributesAndPrices($linkCollection);
            $linkCollection->setPageSize($limit);
        }

        $linkCollection
            ->setVisibility(Mage::getSingleton('Magento_Catalog_Model_Product_Visibility')->getVisibleInCatalogIds())
            ->setFlag('do_not_use_category_id', true);

        $excludeProductIds = $this->getExcludeProductIds();
        if ($excludeProductIds) {
            $linkCollection->addAttributeToFilter('entity_id', array('nin' => $excludeProductIds));
        }

        return $linkCollection;
    }

    /**
     * Get link collection for related and up-sell
     *
     * @return Magento_Catalog_Model_Resource_Product_Collection|null
     */
    protected function _getTargetLinkCollection()
    {
        return $this->_getPreparedTargetLinkCollection($this->getPositionLimit());
    }

    /**
     * Retrieve count of related linked products assigned to product
     *
     * @return int
     */
    public function getLinkCollectionCount()
    {
        return count($this->getLinkCollection()->getItems());
    }

    /**
     * Get target rule collection ids
     *
     * @param null|int $limit
     * @return array
     */
    protected function _getTargetRuleProductIds($limit = null)
    {
        $excludeProductIds = $this->getExcludeProductIds();
        if (!is_null($this->_items)) {
            $excludeProductIds = array_merge(array_keys($this->_items), $excludeProductIds);
        }
        $indexModel = $this->_getTargetRuleIndex()
            ->setType($this->getProductListType())
            ->setLimit($limit)
            ->setProduct($this->getProduct())
            ->setExcludeProductIds($excludeProductIds);
        if (!is_null($limit)) {
            $indexModel->setLimit($limit);
        }

        return $indexModel->getProductIds();
    }

    /**
     * Get target rule collection for related and up-sell
     *
     * @return array
     */
    protected function _getTargetRuleProducts()
    {
        $limit = $this->getPositionLimit();

        $productIds = $this->_getTargetRuleProductIds($limit);

        $items = array();
        if ($productIds) {
            /** @var $collection Magento_Catalog_Model_Resource_Product_Collection */
            $collection = Mage::getResourceModel('Magento_Catalog_Model_Resource_Product_Collection');
            $collection->addFieldToFilter('entity_id', array('in' => $productIds));
            $this->_addProductAttributesAndPrices($collection);

            $collection->setPageSize($limit)
                ->setFlag('do_not_use_category_id', true)
                ->setVisibility(Mage::getSingleton('Magento_Catalog_Model_Product_Visibility')->getVisibleInCatalogIds());

            foreach ($collection as $item) {
                $items[$item->getEntityId()] = $item;
            }
        }

        return $items;
    }

    /**
     * Check is has items
     *
     * @return bool
     */
    public function hasItems()
    {
        return $this->getItemsCount() > 0;
    }

    /**
     * Retrieve count of product in collection
     *
     * @return int
     */
    public function getItemsCount()
    {
        return count($this->getItemCollection());
    }

    /**
     * Get ids of all assigned products
     *
     * @return array
     */
    public function getAllIds()
    {
        if (is_null($this->_allProductIds)) {
            if (!$this->isShuffled()) {
                $this->_allProductIds = array_keys($this->getItemCollection());
                return $this->_allProductIds;
            }

            $targetRuleProductIds = $this->_getTargetRuleProductIds();
            $linkProductCollection = $this->_getPreparedTargetLinkCollection();
            $linkProductIds = array();
            foreach ($linkProductCollection as $item) {
                $linkProductIds[] = $item->getEntityId();
            }
            $this->_allProductIds = array_unique(array_merge($targetRuleProductIds, $linkProductIds));
            shuffle($this->_allProductIds);
        }

        return $this->_allProductIds;
    }
}
