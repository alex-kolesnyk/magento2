<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Checkout
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Shopping cart model
 *
 * @category    Magento
 * @package     Magento_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Checkout\Model;

class Cart extends \Magento\Object implements \Magento\Checkout\Model\Cart\CartInterface
{
    /**
     * Shopping cart items summary quantity(s)
     *
     * @var int|null
     */
    protected $_summaryQty;

    /**
     * List of product ids in shopping cart
     *
     * @var array|null
     */
    protected $_productIds;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Core\Model\Event\Manager
     */
    protected $_eventManager = null;

    /**
     * @param \Magento\Core\Model\Event\Manager $eventManager
     */
    public function __construct(
        \Magento\Core\Model\Event\Manager $eventManager
    ) {
        $this->_eventManager = $eventManager;
    }

    /**
     * Get shopping cart resource model
     *
     * @return \Magento\Checkout\Model\Resource\Cart
     */
    protected function _getResource()
    {
        return \Mage::getResourceSingleton('Magento\Checkout\Model\Resource\Cart');
    }

    /**
     * Retrieve checkout session model
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        return \Mage::getSingleton('Magento\Checkout\Model\Session');
    }

    /**
     * Retrieve customer session model
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomerSession()
    {
        return \Mage::getSingleton('Magento\Customer\Model\Session');
    }

    /**
     * List of shopping cart items
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection|array
     */
    public function getItems()
    {
        if (!$this->getQuote()->getId()) {
            return array();
        }
        return $this->getQuote()->getItemsCollection();
    }

    /**
     * Retrieve array of cart product ids
     *
     * @return array
     */
    public function getQuoteProductIds()
    {
        $products = $this->getData('product_ids');
        if (is_null($products)) {
            $products = array();
            foreach ($this->getQuote()->getAllItems() as $item) {
                $products[$item->getProductId()] = $item->getProductId();
            }
            $this->setData('product_ids', $products);
        }
        return $products;
    }

    /**
     * Get quote object associated with cart. By default it is current customer session quote
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        if (!$this->hasData('quote')) {
            $this->setData('quote', $this->getCheckoutSession()->getQuote());
        }
        return $this->_getData('quote');
    }

    /**
     * Set quote object associated with the cart
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @return \Magento\Checkout\Model\Cart
     */
    public function setQuote(\Magento\Sales\Model\Quote $quote)
    {
        $this->setData('quote', $quote);
        return $this;
    }

    /**
     * Initialize cart quote state to be able use it on cart page
     *
     * @return \Magento\Checkout\Model\Cart
     */
    public function init()
    {
        $quote = $this->getQuote()->setCheckoutMethod('');

        if ($this->getCheckoutSession()->getCheckoutState() !== \Magento\Checkout\Model\Session::CHECKOUT_STATE_BEGIN) {
            $quote->removeAllAddresses()->removePayment();
            $this->getCheckoutSession()->resetCheckout();
        }

        if (!$quote->hasItems()) {
            $quote->getShippingAddress()->setCollectShippingRates(false)
                ->removeAllShippingRates();
        }

        return $this;
    }

    /**
     * Convert order item to quote item
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param mixed $qtyFlag if is null set product qty like in order
     * @return \Magento\Checkout\Model\Cart
     */
    public function addOrderItem($orderItem, $qtyFlag=null)
    {
        /* @var $orderItem \Magento\Sales\Model\Order\Item */
        if (is_null($orderItem->getParentItem())) {
            $product = \Mage::getModel('Magento\Catalog\Model\Product')
                ->setStoreId(\Mage::app()->getStore()->getId())
                ->load($orderItem->getProductId());
            if (!$product->getId()) {
                return $this;
            }

            $info = $orderItem->getProductOptionByCode('info_buyRequest');
            $info = new \Magento\Object($info);
            if (is_null($qtyFlag)) {
                $info->setQty($orderItem->getQtyOrdered());
            } else {
                $info->setQty(1);
            }

            $this->addProduct($product, $info);
        }
        return $this;
    }

    /**
     * Get product object based on requested product information
     *
     * @param   mixed $productInfo
     * @return  \Magento\Catalog\Model\Product
     */
    protected function _getProduct($productInfo)
    {
        $product = null;
        if ($productInfo instanceof \Magento\Catalog\Model\Product) {
            $product = $productInfo;
        } elseif (is_int($productInfo) || is_string($productInfo)) {
            $product = \Mage::getModel('Magento\Catalog\Model\Product')
                ->setStoreId(\Mage::app()->getStore()->getId())
                ->load($productInfo);
        }
        $currentWebsiteId = \Mage::app()->getStore()->getWebsiteId();
        if (!$product
            || !$product->getId()
            || !is_array($product->getWebsiteIds())
            || !in_array($currentWebsiteId, $product->getWebsiteIds())
        ) {
            \Mage::throwException(__('We can\'t find the product.'));
        }
        return $product;
    }

    /**
     * Get request for product add to cart procedure
     *
     * @param   mixed $requestInfo
     * @return  \Magento\Object
     */
    protected function _getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof \Magento\Object) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new \Magento\Object(array('qty' => $requestInfo));
        } else {
            $request = new \Magento\Object($requestInfo);
        }

        if (!$request->hasQty()) {
            $request->setQty(1);
        }

        return $request;
    }

    /**
     * Add product to shopping cart (quote)
     *
     * @param   int|\Magento\Catalog\Model\Product $productInfo
     * @param   mixed $requestInfo
     * @return  \Magento\Checkout\Model\Cart
     */
    public function addProduct($productInfo, $requestInfo=null)
    {
        $product = $this->_getProduct($productInfo);
        $request = $this->_getProductRequest($requestInfo);

        $productId = $product->getId();

        if ($product->getStockItem()) {
            $minimumQty = $product->getStockItem()->getMinSaleQty();
            //If product was not found in cart and there is set minimal qty for it
            if ($minimumQty && $minimumQty > 0 && $request->getQty() < $minimumQty
                && !$this->getQuote()->hasProductId($productId)
            ){
                $request->setQty($minimumQty);
            }
        }

        if ($productId) {
            try {
                $result = $this->getQuote()->addProduct($product, $request);
            } catch (\Magento\Core\Exception $e) {
                $this->getCheckoutSession()->setUseNotice(false);
                $result = $e->getMessage();
            }
            /**
             * String we can get if prepare process has error
             */
            if (is_string($result)) {
                $redirectUrl = ($product->hasOptionsValidationFail())
                    ? $product->getUrlModel()->getUrl(
                        $product,
                        array('_query' => array('startcustomization' => 1))
                    )
                    : $product->getProductUrl();
                $this->getCheckoutSession()->setRedirectUrl($redirectUrl);
                if ($this->getCheckoutSession()->getUseNotice() === null) {
                    $this->getCheckoutSession()->setUseNotice(true);
                }
                \Mage::throwException($result);
            }
        } else {
            \Mage::throwException(__('The product does not exist.'));
        }

        $this->_eventManager->dispatch('checkout_cart_product_add_after', array(
            'quote_item' => $result,
            'product' => $product,
        ));
        $this->getCheckoutSession()->setLastAddedProductId($productId);
        return $this;
    }

    /**
     * Adding products to cart by ids
     *
     * @param   array $productIds
     * @return  \Magento\Checkout\Model\Cart
     */
    public function addProductsByIds($productIds)
    {
        $allAvailable = true;
        $allAdded     = true;

        if (!empty($productIds)) {
            foreach ($productIds as $productId) {
                $productId = (int) $productId;
                if (!$productId) {
                    continue;
                }
                $product = $this->_getProduct($productId);
                if ($product->getId() && $product->isVisibleInCatalog()) {
                    try {
                        $this->getQuote()->addProduct($product);
                    } catch (\Exception $e){
                        $allAdded = false;
                    }
                } else {
                    $allAvailable = false;
                }
            }

            if (!$allAvailable) {
                $this->getCheckoutSession()->addError(
                    __("We don't have some of the products you want.")
                );
            }
            if (!$allAdded) {
                $this->getCheckoutSession()->addError(
                    __("We don't have as many of some products as you want.")
                );
            }
        }
        return $this;
    }

    /**
     * Returns suggested quantities for items.
     * Can be used to automatically fix user entered quantities before updating cart
     * so that cart contains valid qty values
     *
     * $data is an array of ($quoteItemId => (item info array with 'qty' key), ...)
     *
     * @param   array $data
     * @return  array
     */
    public function suggestItemsQty($data)
    {
        foreach ($data as $itemId => $itemInfo) {
            if (!isset($itemInfo['qty'])) {
                continue;
            }
            $qty = (float) $itemInfo['qty'];
            if ($qty <= 0) {
                continue;
            }

            $quoteItem = $this->getQuote()->getItemById($itemId);
            if (!$quoteItem) {
                continue;
            }

            $product = $quoteItem->getProduct();
            if (!$product) {
                continue;
            }

            /* @var $stockItem \Magento\CatalogInventory\Model\Stock\Item */
            $stockItem = $product->getStockItem();
            if (!$stockItem) {
                continue;
            }

            $data[$itemId]['before_suggest_qty'] = $qty;
            $data[$itemId]['qty'] = $stockItem->suggestQty($qty);
        }

        return $data;
    }

    /**
     * Update cart items information
     *
     * @param   array $data
     * @return  \Magento\Checkout\Model\Cart
     */
    public function updateItems($data)
    {
        $this->_eventManager->dispatch('checkout_cart_update_items_before', array('cart'=>$this, 'info'=>$data));

        /* @var $messageFactory \Magento\Core\Model\Message */
        $messageFactory = \Mage::getSingleton('Magento\Core\Model\Message');
        $session = $this->getCheckoutSession();
        $qtyRecalculatedFlag = false;
        foreach ($data as $itemId => $itemInfo) {
            $item = $this->getQuote()->getItemById($itemId);
            if (!$item) {
                continue;
            }

            if (!empty($itemInfo['remove']) || (isset($itemInfo['qty']) && $itemInfo['qty']=='0')) {
                $this->removeItem($itemId);
                continue;
            }

            $qty = isset($itemInfo['qty']) ? (float) $itemInfo['qty'] : false;
            if ($qty > 0) {
                $item->setQty($qty);

                $itemInQuote = $this->getQuote()->getItemById($item->getId());

                if (!$itemInQuote && $item->getHasError()) {
                    \Mage::throwException($item->getMessage());
                }

                if (isset($itemInfo['before_suggest_qty']) && ($itemInfo['before_suggest_qty'] != $qty)) {
                    $qtyRecalculatedFlag = true;
                    $message = $messageFactory->notice(__('Quantity was recalculated from %1 to %2', $itemInfo['before_suggest_qty'], $qty));
                    $session->addQuoteItemMessage($item->getId(), $message);
                }
            }
        }

        if ($qtyRecalculatedFlag) {
            $session->addNotice(
                __('Some products quantities were recalculated because of quantity increment mismatch.')
            );
        }

        $this->_eventManager->dispatch('checkout_cart_update_items_after', array('cart'=>$this, 'info'=>$data));
        return $this;
    }

    /**
     * Remove item from cart
     *
     * @param   int $itemId
     * @return  \Magento\Checkout\Model\Cart
     */
    public function removeItem($itemId)
    {
        $this->getQuote()->removeItem($itemId);
        return $this;
    }

    /**
     * Save cart
     *
     * @return \Magento\Checkout\Model\Cart
     */
    public function save()
    {
        $this->_eventManager->dispatch('checkout_cart_save_before', array('cart'=>$this));

        $this->getQuote()->getBillingAddress();
        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        $this->getQuote()->collectTotals();
        $this->getQuote()->save();
        $this->getCheckoutSession()->setQuoteId($this->getQuote()->getId());
        /**
         * Cart save usually called after changes with cart items.
         */
        $this->_eventManager->dispatch('checkout_cart_save_after', array('cart'=>$this));
        return $this;
    }

    /**
     * Save cart (implement interface method)
     */
    public function saveQuote()
    {
        $this->save();
    }

    /**
     * Mark all quote items as deleted (empty shopping cart)
     *
     * @return \Magento\Checkout\Model\Cart
     */
    public function truncate()
    {
        $this->getQuote()->removeAllItems();
        return $this;
    }

    public function getProductIds()
    {
        $quoteId = \Mage::getSingleton('Magento\Checkout\Model\Session')->getQuoteId();
        if (null === $this->_productIds) {
            $this->_productIds = array();
            if ($this->getSummaryQty()>0) {
               foreach ($this->getQuote()->getAllItems() as $item) {
                   $this->_productIds[] = $item->getProductId();
               }
            }
            $this->_productIds = array_unique($this->_productIds);
        }
        return $this->_productIds;
    }

    /**
     * Get shopping cart items summary (includes config settings)
     *
     * @return int|float
     */
    public function getSummaryQty()
    {
        $quoteId = \Mage::getSingleton('Magento\Checkout\Model\Session')->getQuoteId();

        //If there is no quote id in session trying to load quote
        //and get new quote id. This is done for cases when quote was created
        //not by customer (from backend for example).
        if (!$quoteId && \Mage::getSingleton('Magento\Customer\Model\Session')->isLoggedIn()) {
            $quote = \Mage::getSingleton('Magento\Checkout\Model\Session')->getQuote();
            $quoteId = \Mage::getSingleton('Magento\Checkout\Model\Session')->getQuoteId();
        }

        if ($quoteId && $this->_summaryQty === null) {
            if (\Mage::getStoreConfig('checkout/cart_link/use_qty')) {
                $this->_summaryQty = $this->getItemsQty();
            } else {
                $this->_summaryQty = $this->getItemsCount();
            }
        }
        return $this->_summaryQty;
    }

    /**
     * Get shopping cart items count
     *
     * @return int
     */
    public function getItemsCount()
    {
        return $this->getQuote()->getItemsCount()*1;
    }

    /**
     * Get shopping cart summary qty
     *
     * @return int|float
     */
    public function getItemsQty()
    {
        return $this->getQuote()->getItemsQty()*1;
    }

    /**
     * Update item in shopping cart (quote)
     * $requestInfo - either qty (int) or buyRequest in form of array or \Magento\Object
     * $updatingParams - information on how to perform update, passed to Quote->updateItem() method
     *
     * @param int $itemId
     * @param int|array|\Magento\Object $requestInfo
     * @param null|array|\Magento\Object $updatingParams
     * @return \Magento\Sales\Model\Quote\Item|string
     *
     * @see \Magento\Sales\Model\Quote::updateItem()
     */
    public function updateItem($itemId, $requestInfo = null, $updatingParams = null)
    {
        try {
            $item = $this->getQuote()->getItemById($itemId);
            if (!$item) {
                \Mage::throwException(__('This quote item does not exist.'));
            }
            $productId = $item->getProduct()->getId();
            $product = $this->_getProduct($productId);
            $request = $this->_getProductRequest($requestInfo);

            if ($product->getStockItem()) {
                $minimumQty = $product->getStockItem()->getMinSaleQty();
                // If product was not found in cart and there is set minimal qty for it
                if ($minimumQty && ($minimumQty > 0)
                    && ($request->getQty() < $minimumQty)
                    && !$this->getQuote()->hasProductId($productId)
                ) {
                    $request->setQty($minimumQty);
                }
            }

            $result = $this->getQuote()->updateItem($itemId, $request, $updatingParams);
        } catch (\Magento\Core\Exception $e) {
            $this->getCheckoutSession()->setUseNotice(false);
            $result = $e->getMessage();
        }

        /**
         * We can get string if updating process had some errors
         */
        if (is_string($result)) {
            if ($this->getCheckoutSession()->getUseNotice() === null) {
                $this->getCheckoutSession()->setUseNotice(true);
            }
            \Mage::throwException($result);
        }

        $this->_eventManager->dispatch('checkout_cart_product_update_after', array(
            'quote_item' => $result,
            'product' => $product
        ));
        $this->getCheckoutSession()->setLastAddedProductId($productId);
        return $result;
    }
}
