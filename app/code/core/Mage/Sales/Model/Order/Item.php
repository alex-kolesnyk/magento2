<?php

class Mage_Sales_Model_Order_Item extends Mage_Core_Model_Abstract
{

    const STATUS_PENDING        = 1; // No items backordered, shipped, or returned (may have canceled qty)
    const STATUS_SHIPPED        = 2; // When qty ordered - [qty canceled + qty returned] = qty shipped
    const STATUS_BACKORDERED    = 3; // When qty ordered - [qty canceled + qty returned] = qty backordered
    const STATUS_RETURNED       = 4; // When qty ordered = qty returned
    const STATUS_CANCELED       = 5; // When qty ordered = qty canceled
    const STATUS_PARTIAL        = 6; // If [qty shipped + qty canceled + qty returned] < qty ordered
    const STATUS_MIXED          = 7; // All other combinations

    protected static $_statuses = null;

    protected $_order;

    protected $_product = null;

    protected function _construct()
    {
        $this->_init('sales/order_item');
    }

    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    public function getOrder()
    {
        if (is_null($this->_order) && ($orderId = $this->getParentId())) {
            $order = Mage::getModel('sales/order');
            /* @var $order Mage_Sales_Model_Order */
            $order->load($orderId);
            $this->setOrder($order);
        }
        return $this->_order;
    }

    public function importQuoteItem(Mage_Sales_Model_Quote_Item $item)
    {
        $this->setQuoteItemId($item->getId())
            ->setStoreId($item->getQuote()->getStoreId())
            ->setProductId($item->getProductId())
            ->setSku($item->getSku())
            ->setImage($item->getImage())
            ->setName($item->getName())
            ->setDescription($item->getDescription())
            ->setQtyOrdered($item->getQty())
            ->setPrice($item->getPrice())
            ->setRowTotal($item->getRowTotal())
            // TODO - all others
        ;
        return $this;
    }

    public function importQuoteAddressItem(Mage_Sales_Model_Quote_Address_Item $item)
    {
        $this->setQuoteItemId($item->getAddress()->getQuote()->getId())
            ->setStoreId($item->getAddress()->getQuote()->getStoreId())
            ->setProductId($item->getProductId())
            ->setSku($item->getSku())
            ->setImage($item->getImage())
            ->setName($item->getName())
            ->setDescription($item->getDescription())
            ->setQtyOrdered($item->getQty())
            ->setPrice($item->getPrice())
            ->setRowTotal($item->getRowTotal())
            // TODO - all others
        ;
        return $this;
    }

    public function getStatusId()
    {
        if (!$this->getQtyBackordered() && !$this->getQtyShipped() && !$this->getQtyReturned()) {
            return self::STATUS_PENDING;
        } elseif ( ( $this->getQtyOrdered() - ($this->getQtyCanceled() + $this->getQtyReturned()) ) == $this->getQtyShipped() ) {
            return self::STATUS_SHIPPED;
        } elseif ( ( $this->getQtyOrdered() - ($this->getQtyCanceled() + $this->getQtyReturned()) ) == $this->getQtyBackordered() ) {
            return self::STATUS_BACKORDERED;
        } elseif ( $this->getQtyOrdered() == $this->getQtyReturned() ) {
            return self::STATUS_RETURNED;
        } elseif ( $this->getQtyOrdered() == $this->getQtyCanceled() ) {
            return self::STATUS_CANCELED;
        } elseif ( ( $this->getQtyShipped() + $this->getQtyCanceled() + $this->getQtyReturned() ) < $this->getQtyOrdered() ) {
            return self::STATUS_PARTIAL;
        } else {
            return self::STATUS_MIXED;
        }
    }

    public function getStatus()
    {
        // echo ( $this->getQtyOrdered() - ($this->getQtyCanceled() + $this->getQtyReturned()) ) . ' == ' .  $this->getQtyShipped() . ' ? ' . $this->getStatusId() . ':' . $this->getStatusName($this->getStatusId()) . '<br>';
        return $this->getStatusName($this->getStatusId());
    }

    public static function getStatuses()
    {
        if (is_null(self::$_statuses)) {
            self::$_statuses = array(
                self::STATUS_PENDING        => __('Pending'),
                self::STATUS_SHIPPED        => __('Shipped'),
                self::STATUS_BACKORDERED    => __('Backordered'),
                self::STATUS_RETURNED       => __('Returned'),
                self::STATUS_CANCELED       => __('Canceled'),
                self::STATUS_PARTIAL        => __('Partial'),
                self::STATUS_MIXED          => __('Mixed'),
            );
        }
        return self::$_statuses;
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public static function getStatusName($statusId)
    {
        if (is_null(self::$_statuses)) {
            self::getStatuses();
        }
        if (isset(self::$_statuses[$statusId])) {
            return self::$_statuses[$statusId];
        }
        return __('Unknown Status');
    }

//    public function canBeShipped()
//    {
//        $canBeShipped = array(
//            self::STATUS_PENDING,
//            self::STATUS_BACKORDERED,
//            self::STATUS_PARTIAL,
//            self::STATUS_MIXED,
//        );
//        if (in_array($this->getStatusId(), $canBeShipped)) {
//            return true;
//        }
//        return false;
//    }

    /**
     * Enter description here...
     *
     * @return float|integer
     */
    public function getQtyToShip()
    {
        return max($this->getQtyOrdered() - $this->getQtyShipped() - $this->getQtyReturned() - $this->getQtyCanceled(), 0);
    }

    /**
     * Enter description here...
     *
     * @return Mage_Sales_Model_Order_Item
     */
    public function calcRowTotal()
    {
        $this->setRowTotal($this->getPrice()*$this->getQty());
        return $this;
    }

    /**
     * Enter description here...
     *
     * @return Mage_Sales_Model_Order_Item
     */
    public function calcRowWeight()
    {
        $this->setRowWeight($this->getWeight()*$this->getQty());
        return $this;
    }

    /**
     * Enter description here...
     *
     * @return Mage_Sales_Model_Order_Item
     */
    public function calcTaxAmount()
    {
        $this->setTaxAmount($this->getRowTotal() * $this->getTaxPercent()/100);
        return $this;
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getPriceFormatted()
    {
        return $this->getOrder()->getOrderCurrency()->format($this->getPrice());
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getRowTotalFormatted()
    {
        return $this->getOrder()->getOrderCurrency()->format($this->getRowTotal());
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getTaxAmountFormatted()
    {
        if ($this->getTaxAmount()) {
            return $this->getOrder()->getOrderCurrency()->format($this->getTaxAmount());
        }
        return '-';
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getDiscountAmountFormatted()
    {
        if ($this->getDiscountAmount()) {
            return $this->getOrder()->getOrderCurrency()->format($this->getDiscountAmount());
        }
        return '-';
    }

    /**
     * Enter description here...
     *
     * @return Mage_Sales_Model_Order_Item
     */
    public function cancel()
    {
        $this->setQtyCanceled($this->getQtyToShip());
        return $this;
    }

}
