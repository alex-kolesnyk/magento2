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
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer account form block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Adminhtml_Block_Customer_Edit_Tab_View extends Mage_Core_Block_Template
{
    const ONLINE_INTERVAL = 900; // 15 min
    protected $_customer;
    protected $_customerLog;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('customer/tab/view.phtml');
    }

    protected function _initChildren()
    {
        $this->setChild('sales', $this->getLayout()->createBlock('adminhtml/customer_edit_tab_view_sales'));

        $accordion = $this->getLayout()->createBlock('adminhtml/widget_accordion')
            ->setId('customerViewAccordion')
            //->setShowOnlyOne(0)
            ;

        /* @var $accordion Mage_Adminhtml_Block_Widget_Accordion */
        $accordion->addItem('lastOrders', array(
            'title'     => __('Recent Orders'),
            'content'   => $this->getLayout()->createBlock('adminhtml/customer_edit_tab_view_orders'),
            'open'      => true
        ));

        $accordion->addItem('shopingCart', array(
            'title' => __('Shopping Cart'),
            'content' => $this->getLayout()->createBlock('adminhtml/customer_edit_tab_view_cart'),
        ));

        $accordion->addItem('wishlist', array(
            'title' => __('Wishlist'),
            'content' => $this->getLayout()->createBlock('adminhtml/customer_edit_tab_view_wishlist'),
        ));

        $this->setChild('accordion', $accordion);
    }

    public function getCustomer()
    {
        if (!$this->_customer) {
            $this->_customer = Mage::registry('current_customer');
        }
        return $this->_customer;
    }

    public function getCustomerLog()
    {
        if (!$this->_customerLog) {
            $this->_customerLog = Mage::getModel('log/customer')
                ->load($this->getCustomer()->getId());

        }
        return $this->_customerLog;
    }

    public function getFormat()
    {
        return $this->_dateTimeFormat;
    }

    public function getCreateDate()
    {
        return strftime(Mage::getStoreConfig('general/local/datetime_format_medium'), strtotime($this->getCustomer()->getCreatedAt()));
    }

    public function getLastLoginDate()
    {
        if ($date = $this->getCustomerLog()->getLoginAt()) {
            return strftime(Mage::getStoreConfig('general/local/datetime_format_medium'), strtotime($date));
        }
        return __('Never');
    }

    public function getCurrentStatus()
    {
        $log = $this->getCustomerLog();
        if ($log->getLogoutAt() || strtotime(now())-strtotime($log->getLastVisitAt())>self::ONLINE_INTERVAL) {
            return __('Offline');
        }
        return __('Online');
    }

    public function getCreatedInStore()
    {
        return Mage::getModel('core/store')->load($this->getCustomer()->getStoreId())->getName();
    }

    public function getBillingAddressHtml()
    {
        $html = '';
        if ($address = $this->getCustomer()->getPrimaryBillingAddress()) {
            $html = $address->toString($address->getHtmlFormat());
        }
        else {
            $html = __('Customer doesn\'t have primary billing address');
        }
        return $html;
    }

    public function getAccordionHtml()
    {
        return $this->getChildHtml('accordion');
    }

    public function getSalesHtml()
    {
        return $this->getChildHtml('sales');
    }

}
