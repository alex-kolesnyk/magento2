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
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Sales_Model_Order_Invoice_Item extends Mage_Core_Model_Abstract
{
    protected $_invoice;
    protected $_orderItem;

    /**
     * Initialize resource model
     */
    function _construct()
    {
        $this->_init('sales/order_invoice_item');
    }

    /**
     * Declare invoice instance
     *
     * @param   Mage_Sales_Model_Order_Invoice $invoice
     * @return  Mage_Sales_Model_Order_Invoice_Item
     */
    public function setInvoice(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $this->_invoice = $invoice;
        return $this;
    }

    /**
     * Retrieve invoice instance
     *
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function getInvoice()
    {
        return $this->_invoice;
    }

    public function calcRowTotal()
    {
        $this->setRowTotal($this->getPrice()*$this->getQty());
        return $this;
    }

    public function calcRowWeight()
    {
        $this->setRowWeight($this->getWeight()*$this->getQty());
        return $this;
    }

    public function calcTaxAmount()
    {
        $this->setTaxAmount($this->getRowTotal() * $this->getTaxPercent()/100);
        return $this;
    }

}