<?php
/**
 * Sales order history block
 *
 * @package    Mage
 * @subpackage Sales
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Michael Bessolov <michael@varien.com>
 */

class Mage_Sales_Block_Order_Recent extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToSelect('*')
            ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id')
            ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id')
            ->addAttributeToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
        ;

        $this->setOrders($orders);
    }

    public function getViewUrl($order)
    {
        return $this->getUrl('*/*/view', array('order_id' => $order->getId()));
    }

    public function getTrackUrl($order)
    {
        return $this->getUrl('*/*/track', array('order_id' => $order->getId()));
    }

    public function getOrderDateFormatted($order, $format='short')
    {
        $dateFormatted = strftime(Mage::getStoreConfig('general/local/date_format_' . $format), strtotime($order->getCreatedAt()));
        return $dateFormatted;
    }

}
