<?php
/**
 * Sales order view block
 *
 * @package    Mage
 * @subpackage Sales
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Michael Bessolov <michael@varien.com>
 */

class Mage_Sales_Block_Order_View extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sales/order/view.phtml');
        $this->setOrder(Mage::getModel('sales/order')->load($this->getRequest()->getParam('order_id')));
        Mage::registry('action')->getLayout()->getBlock('root')->setHeaderTitle(__('Order Invoices'));
    }

    public function getBackUrl()
    {
        return Mage::getUrl('*/*/history');
    }

}
