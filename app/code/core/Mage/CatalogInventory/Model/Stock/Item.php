<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_CatalogInventory
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
/**
 * Stock item model
 *
 * @category   Mage
 * @package    Mage_CatalogInventory
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_CatalogInventory_Model_Stock_Item extends Mage_Core_Model_Abstract
{
    const XML_PATH_MIN_QTY      = 'cataloginventory/options/min_qty';
    const XML_PATH_MIN_SALE_QTY = 'cataloginventory/options/min_sale_qty';
    const XML_PATH_MAX_SALE_QTY = 'cataloginventory/options/max_sale_qty';
    const XML_PATH_BACKORDERS   = 'cataloginventory/options/backorders';
    const XML_PATH_CAN_SUBTRACT = 'cataloginventory/options/can_subtract';
    
    protected function _construct()
    {
        $this->_init('cataloginventory/stock_item');
    }
    
    /**
     * Retrieve stock identifier
     * 
     * @todo multi stock
     * @return int
     */
    public function getStockId()
    {
        return 1;
    }
    
    /**
     * Load item data by product
     *
     * @param   mixed $product
     * @return  Mage_CatalogInventory_Model_Stock_Item
     */
    public function loadByProduct($product)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            $product = $product->getId();
        }
        $this->getResource()->loadByProductId($this, $product);
        $this->setOrigData();
        return $this;
    }
    
    /**
     * Subtract quote item quantity
     *
     * @param   decimal $qty
     * @return  Mage_CatalogInventory_Model_Stock_Item
     */
    public function subtractQty($qty)
    {
        $config = (bool) Mage::app()->getStore()->getConfig(self::XML_PATH_CAN_SUBTRACT);
        if (!$config) {
            return $this;
        }
        
        $this->setQty($this->getQty()-$qty);
        return $this;
    }
    
    public function getStoreId()
    {
        $storeId = $this->getData('store_id');
        if (is_null($storeId)) {
            if ($this->getProduct()) {
                $storeId = $this->getProduct()->getStoreId();
            }
            else {
                $storeId = Mage::app()->getStore()->getId();
            }
            $this->setData('store_id', $storeId);
        }
        return $storeId;
    }
    
    /**
     * Adding stoc data to product
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_CatalogInventory_Model_Stock_Item
     */
    public function assignProduct(Mage_Catalog_Model_Product $product)
    {
        if (!$this->getId() || !$this->getProductId()) {
            $this->getResource()->loadByProductId($this, $product->getId());
        }
        
        $product->setStockItem($this);
        $this->setProduct($product);
        $product->setIsSalable($this->getIsInStock());
        return $this;
    }
    
    /**
     * Retrieve minimal quantity available for item status in stock
     *
     * @return decimal
     */
    public function getMinQty()
    {
        if ($this->getUseConfigMinQty()) {
            return (float) Mage::app()->getStore()->getConfig(self::XML_PATH_MIN_QTY);
        }
        return $this->getData('min_qty');
    }
    
    public function getMinSaleQty()
    {
        if ($this->getUseConfigMinSaleQty()) {
            return (float) Mage::app()->getStore()->getConfig(self::XML_PATH_MIN_SALE_QTY);
        }
        return $this->getData('min_sale_qty');
    }

    public function getMaxSaleQty()
    {
        if ($this->getUseConfigMaxSaleQty()) {
            return (float) Mage::app()->getStore()->getConfig(self::XML_PATH_MAX_SALE_QTY);
        }
        return $this->getData('max_sale_qty');
    }

    /**
     * Retrieve backorders status
     *
     * @return int
     */
    public function getBackorders()
    {
        if ($this->getUseConfigBackorders()) {
            return (int) Mage::app()->getStore()->getConfig(self::XML_PATH_BACKORDERS);
        }
        return $this->getData('backorders');
    }
    
    /**
     * Check quantity
     *
     * @param   decimal $qty
     * @exception Mage_Core_Exception
     * @return  bool
     */
    public function checkQty($qty)
    {
        if ($this->getQty() - $qty < $this->getMinQty()) {
            switch ($this->getBackorders()) {
                case Mage_CatalogInventory_Model_Stock::BACKORDERS_BELOW:
                case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES:
                    break;
                default:
                    if ($this->getProduct()) {
                        Mage::throwException(
                            __('Requested quantity for "%s" is not available.', $this->getProduct()->getName())
                        );
                    }
                    else {
                        Mage::throwException(__('Requested quantity is not available.'));
                    }
                    break;
            }
        }
        return true;
    }
    
    /**
     * Checking quote item quantity
     *
     * @param   Mage_Sales_Model_Quote_Item $item
     * @return  Mage_CatalogInventory_Model_Stock_Item
     */
    public function checkQuoteItemQty(Mage_Sales_Model_Quote_Item $item)
    {
        $qty    = $item->getQty();
        if (!is_numeric($qty)) {
            $qty = floatval($qty);
        }

        if (!$this->getIsInStock()) {
            $this->_addQuoteItemError(
                $item,
                __('This product is out of stock.'),
                __('Some of the products are out of stock'),
                'stock'
            );
            $item->setUseOldQty(true);
            return $this;
        }
        
        if ($this->getMinSaleQty() && $qty<$this->getMinSaleQty()) {
            $this->_addQuoteItemError(
                $item,
                __('Minimum allowed quantity is %d.', $this->getMinSaleQty()),
                __('Some of the products can not be ordered in requested quantity'),
                'qty'
            );
            return $this;
        }
        
        if ($this->getMaxSaleQty() && $qty>$this->getMaxSaleQty()) {
            $this->_addQuoteItemError(
                $item,
                __('Maximum allowed quantity is %d.', $this->getMaxSaleQty()),
                __('Some of the products can not be ordered in requested quantity'),
                'qty'
            );
            return $this;
        }
        
        
        if ($this->checkQty($qty)) {
            if ($this->getBackorders() == Mage_CatalogInventory_Model_Stock::BACKORDERS_YES) {
                if ($this->getProduct()) {
                    $item->setMessage(
                        __('There are only %d "%s" in stock. This item will be backordered.', 
                            $this->getQty(), 
                            $this->getProduct()->getName())
                    );
                }
            }
        }
        
        /**
         * Check quontity type
         */
        if (!$this->getIsQtyDecimal()) {
            $qty = intval($qty);
        }
        
        $item->setHasError(false);
        /**
         * Adding stock data to quote item
         */
        $item->addData(array(
            'qty'       => $qty,
            'backorders'=> $this->getBackorders(),
        ));
        
        return $this;
    }
    
    protected function _addQuoteItemError(Mage_Sales_Model_Quote_Item $item, $itemError, $quoteError, $errorIndex='error')
    {
        $item->setHasError(true);
        $item->setMessage($itemError);
        $item->setQuoteMessage($quoteError);
        $item->setQuoteMessageIndex($errorIndex);
        return $this;
    }
    
    protected function _beforeSave()
    {
        if ($this->getBackorders() == Mage_CatalogInventory_Model_Stock::BACKORDERS_NO 
            && $this->getQty() <= $this->getMinQty()) {
            $this->setIsInStock(false);
        }
        Mage::dispatchEvent('cataloginventory_stock_item_save_before', array('item'=>$this));
        return $this;
    }
}