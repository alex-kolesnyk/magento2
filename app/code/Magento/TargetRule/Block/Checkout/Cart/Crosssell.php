<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_TargetRule
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * TargetRule Checkout Cart Cross-Sell Products Block
 *
 * @category   Magento
 * @package    Magento_TargetRule
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Magento_TargetRule_Block_Checkout_Cart_Crosssell extends Magento_TargetRule_Block_Product_Abstract
{
    /**
     * Default MAP renderer type
     *
     * @var string
     */
    protected $_mapRenderer = 'msrp_item';

    /**
     * Array of product objects in cart
     *
     * @var array
     */
    protected $_products;

    /**
     * object of just added product to cart
     *
     * @var Magento_Catalog_Model_Product
     */
    protected $_lastAddedProduct;

    /**
     * Whether get products by last added
     *
     * @var bool
     */
    protected $_byLastAddedProduct = false;

    /**
     * @var Magento_TargetRule_Model_Index
     */
    protected $_index;

    /**
     * @var Magento_TargetRule_Model_IndexFactory
     */
    protected $_indexFactory;

    /**
     * @var Magento_Catalog_Model_ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Magento_Catalog_Model_Product_LinkFactory
     */
    protected $_productLinkFactory;

    /**
     * @var Magento_Checkout_Model_Session
     */
    protected $_session;

    /**
     * @var Magento_Catalog_Model_Product_Visibility
     */
    protected $_visibility;

    /**
     * @var Magento_CatalogInventory_Model_Stock_Status
     */
    protected $_status;

    /**
     * @var Magento_Core_Model_StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Magento_Catalog_Model_Resource_Product_CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @param Magento_Catalog_Model_Resource_Product_CollectionFactory $productCollectionFactory
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     * @param Magento_Catalog_Model_Product_Visibility $visibility
     * @param Magento_CatalogInventory_Model_Stock_Status $status
     * @param Magento_Checkout_Model_Session $session
     * @param Magento_Catalog_Model_Product_LinkFactory $productLinkFactory
     * @param Magento_Catalog_Model_ProductFactory $productFactory
     * @param Magento_TargetRule_Model_IndexFactory $indexFactory
     * @param Magento_Core_Model_Registry $coreRegistry
     * @param Magento_TargetRule_Helper_Data $targetRuleData
     * @param Magento_Tax_Helper_Data $taxData
     * @param Magento_Catalog_Helper_Data $catalogData
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Core_Block_Template_Context $context
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Magento_Catalog_Model_Resource_Product_CollectionFactory $productCollectionFactory,
        Magento_Core_Model_StoreManagerInterface $storeManager,
        Magento_Catalog_Model_Product_Visibility $visibility,
        Magento_CatalogInventory_Model_Stock_Status $status,
        Magento_Checkout_Model_Session $session,
        Magento_Catalog_Model_Product_LinkFactory $productLinkFactory,
        Magento_Catalog_Model_ProductFactory $productFactory,
        Magento_TargetRule_Model_IndexFactory $indexFactory,
        Magento_Core_Model_Registry $coreRegistry,
        Magento_TargetRule_Helper_Data $targetRuleData,
        Magento_Tax_Helper_Data $taxData,
        Magento_Catalog_Helper_Data $catalogData,
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Block_Template_Context $context,
        array $data = array()
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_visibility = $visibility;
        $this->_status = $status;
        $this->_session = $session;
        $this->_productLinkFactory = $productLinkFactory;
        $this->_productFactory = $productFactory;
        $this->_indexFactory = $indexFactory;
        parent::__construct($coreRegistry, $targetRuleData, $taxData, $catalogData, $coreData, $context, $data);
    }


    /**
     * Retrieve Catalog Product List Type identifier
     *
     * @return int
     */
    public function getProductListType()
    {
        return Magento_TargetRule_Model_Rule::CROSS_SELLS;
    }

    /**
     * Retrieve just added to cart product id
     *
     * @return int|false
     */
    public function getLastAddedProductId()
    {
        return $this->_session->getLastAddedProductId(true);
    }

    /**
     * Retrieve just added to cart product object
     *
     * @return Magento_Catalog_Model_Product
     */
    public function getLastAddedProduct()
    {
        if (is_null($this->_lastAddedProduct)) {
            $productId = $this->getLastAddedProductId();
            if ($productId) {
                $this->_lastAddedProduct = $this->_productFactory->create()->load($productId);
            } else {
                $this->_lastAddedProduct = false;
            }
        }
        return $this->_lastAddedProduct;
    }

    /**
     * Retrieve quote instance
     *
     * @return Magento_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->_session->getQuote();
    }

    /**
     * Retrieve Array of Product instances in Cart
     *
     * @return array
     */
    protected function _getCartProducts()
    {
        if (is_null($this->_products)) {
            $this->_products = array();
            foreach ($this->getQuote()->getAllItems() as $quoteItem) {
                /* @var $quoteItem Magento_Sales_Model_Quote_Item */
                $product = $quoteItem->getProduct();
                $this->_products[$product->getEntityId()] = $product;
            }
        }

        return $this->_products;
    }

    /**
     * Retrieve Array of product ids in Cart
     *
     * @return array
     */
    protected function _getCartProductIds()
    {
        $products = $this->_getCartProducts();
        return array_keys($products);
    }

    /**
     * Retrieve Array of product ids which have special relation with products in Cart
     * For example simple product as part of Grouped product
     *
     * @return array
     */
    protected function _getCartProductIdsRel()
    {
        $productIds = array();
        foreach ($this->getQuote()->getAllItems() as $quoteItem) {
            $productTypeOpt = $quoteItem->getOptionByCode('product_type');
            if ($productTypeOpt instanceof Magento_Sales_Model_Quote_Item_Option
                && $productTypeOpt->getValue() == Magento_Catalog_Model_Product_Type_Grouped::TYPE_CODE
                && $productTypeOpt->getProductId()
            ) {
                $productIds[] = $productTypeOpt->getProductId();
            }
        }

        return $productIds;
    }

    /**
     * Retrieve Target Rule Index instance
     *
     * @return Magento_TargetRule_Model_Index
     */
    protected function _getTargetRuleIndex()
    {
        if (is_null($this->_index)) {
            $this->_index = $this->_indexFactory->create();
        }
        return $this->_index;
    }

    /**
     * Retrieve Maximum Number Of Product
     *
     * @return int
     */
    public function getPositionLimit()
    {
        return $this->_targetRuleData->getMaximumNumberOfProduct(Magento_TargetRule_Model_Rule::CROSS_SELLS);
    }

    /**
     * Retrieve Position Behavior
     *
     * @return int
     */
    public function getPositionBehavior()
    {
        return $this->_targetRuleData->getShowProducts(Magento_TargetRule_Model_Rule::CROSS_SELLS);
    }

    /**
     * Get link collection for cross-sell
     *
     * @throws Magento_Core_Exception
     * @return Magento_Catalog_Model_Resource_Product_Link_Product_Collection|null
     */
    protected function _getTargetLinkCollection()
    {
        /* @var $collection Magento_Catalog_Model_Resource_Product_Link_Product_Collection */
        $collection = $this->_productLinkFactory->create()
            ->useCrossSellLinks()
            ->getProductCollection()
            ->setStoreId($this->_storeManager->getStore()->getId())
            ->setGroupBy();
        $this->_addProductAttributesAndPrices($collection);
        $collection->setVisibility($this->_visibility->getVisibleInSiteIds());
        $this->_status->addIsInStockFilterToCollection($collection);

        return $collection;
    }

    /**
     * Retrieve array of cross-sell products for just added product to cart
     *
     * @return array
     */
    protected function _getProductsByLastAddedProduct()
    {
        $product = $this->getLastAddedProduct();
        if (!$product) {
            return array();
        }
        $this->_byLastAddedProduct = true;
        $items = parent::getItemCollection();
        $this->_byLastAddedProduct = false;
        $this->_items = null;
        return $items;
    }

    /**
     * Retrieve Product Ids from Cross-sell rules based products index by product object
     *
     * @param Magento_Catalog_Model_Product $product
     * @param int $limit
     * @param array $excludeProductIds
     * @return array
     */
    protected function _getProductIdsFromIndexByProduct($product, $count, $excludeProductIds = array())
    {
        return $this->_getTargetRuleIndex()
            ->setType(Magento_TargetRule_Model_Rule::CROSS_SELLS)
            ->setLimit($count)
            ->setProduct($product)
            ->setExcludeProductIds($excludeProductIds)
            ->getProductIds();
    }

    /**
     * Retrieve Product Collection by Product Ids
     *
     * @param array $productIds
     * @return Magento_Catalog_Model_Resource_Product_Collection
     */
    protected function _getProductCollectionByIds($productIds)
    {
        /* @var $collection Magento_Catalog_Model_Resource_Product_Collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', array('in' => $productIds));
        $this->_addProductAttributesAndPrices($collection);

        $collection->setVisibility($this->_visibility->getVisibleInCatalogIds());
        $this->_status->addIsInStockFilterToCollection($collection);

        return $collection;
    }

    /**
     * Retrieve Product Ids from Cross-sell rules based products index by products in shopping cart
     *
     * @param Magento_Catalog_Model_Product $product
     * @param int $limit
     * @param array $excludeProductIds
     * @return array
     */
    protected function _getProductIdsFromIndexForCartProducts($limit, $excludeProductIds = array())
    {
        $resultIds = array();

        foreach ($this->_getCartProducts() as $product) {
            if ($product->getEntityId() == $this->getLastAddedProductId()) {
                continue;
            }

            $productIds = $this
                ->_getProductIdsFromIndexByProduct($product, $this->getPositionLimit(), $excludeProductIds);
            $resultIds = array_merge($resultIds, $productIds);
        }

        $resultIds = array_unique($resultIds);
        shuffle($resultIds);

        return array_slice($resultIds, 0, $limit);
    }

    /**
     * Get exclude product ids
     *
     * @return array
     */
    protected function _getExcludeProductIds()
    {
        $excludeProductIds = $this->_getCartProductIds();
        if (!is_null($this->_items)) {
            $excludeProductIds = array_merge(array_keys($this->_items), $excludeProductIds);
        }
        return $excludeProductIds;
    }

    /**
     * Get target rule based products for cross-sell
     *
     * @return array
     */
    protected function _getTargetRuleProducts()
    {
        $excludeProductIds = $this->_getExcludeProductIds();
        $limit = $this->getPositionLimit();
        $productIds = $this->_byLastAddedProduct
            ? $this->_getProductIdsFromIndexByProduct($this->getLastAddedProduct(), $limit, $excludeProductIds)
            : $this->_getProductIdsFromIndexForCartProducts($limit, $excludeProductIds);

        $items = array();
        if ($productIds) {
            $collection = $this->_getProductCollectionByIds($productIds);
            foreach ($collection as $product) {
                $items[$product->getEntityId()] = $product;
            }
        }

        return $items;
    }

    /**
     * Get linked products
     *
     * @return array
     */
    protected function _getLinkProducts()
    {
        $items = array();
        $collection = $this->getLinkCollection();
        if ($collection) {
            if ($this->_byLastAddedProduct) {
                $collection->addProductFilter($this->getLastAddedProduct()->getEntityId());
            } else {
                $filterProductIds = array_merge($this->_getCartProductIds(), $this->_getCartProductIdsRel());
                $collection->addProductFilter($filterProductIds);
            }
            $collection->addExcludeProductFilter($this->_getExcludeProductIds());

            foreach ($collection as $product) {
                $items[$product->getEntityId()] = $product;
            }
        }
        return $items;
    }

    /**
     * Retrieve array of cross-sell products
     *
     * @return array
     */
    public function getItemCollection()
    {
        if (is_null($this->_items)) {
            // if has just added product to cart - load cross-sell products for it
            $productsByLastAdded = $this->_getProductsByLastAddedProduct();
            $limit = $this->getPositionLimit();
            if (count($productsByLastAdded) < $limit) {
                // reset collection
                $this->_linkCollection = null;
                parent::getItemCollection();
                // products by last added are preferable
                $this->_items = $productsByLastAdded + $this->_items;
                $this->_sliceItems();
            } else {
                $this->_items = $productsByLastAdded;
            }
            $this->_orderProductItems();
        }
        return $this->_items;
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
}
