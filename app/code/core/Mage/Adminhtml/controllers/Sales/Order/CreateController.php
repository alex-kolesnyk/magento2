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
 * Adminhtml sales orders creation process controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 * @author     Michael Bessolov <michael@varien.com>
 */
class Mage_Adminhtml_Sales_Order_CreateController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Retrieve session object
     *
     * @return Mage_Adminhtml_Model_Quote
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session_quote');
    }

    /**
     * Retrieve quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getSession()->getQuote();
    }

    /**
     * Retrieve order create model
     *
     * @return Mage_Adminhtml_Model_Sales_Order_Create
     */
    protected function _getOrderCreateModel()
    {
        return Mage::getSingleton('adminhtml/sales_order_create');
    }

    /**
     * Initialize order creation session data
     *
     * @return Mage_Adminhtml_Sales_Order_CreateController
     */
    protected function _initSession()
    {
        /**
         * Identify customer
         */
        if ($customerId = $this->getRequest()->getParam('customer_id')) {
            $this->_getSession()->setCustomerId((int) $customerId);
        }

        /**
         * Identify store
         */
        if ($storeId = $this->getRequest()->getParam('store_id')) {
            $this->_getSession()->setStoreId((int) $storeId);
        }

        /**
         * Identify currency
         */
        if ($currencyId = $this->getRequest()->getParam('currency_id')) {
            $this->_getSession()->setCurrencyId((string) $currencyId);
            $this->_getOrderCreateModel()->setRecollect(true);
        }
        return $this;
    }

    /**
     * Processing request data
     *
     * @return Mage_Adminhtml_Sales_Order_CreateController
     */
    protected function _processData()
    {
        /**
         * Saving order data
         */
        if ($data = $this->getRequest()->getPost('order')) {
            $this->_getOrderCreateModel()->importPostData($data);
        }

        /**
         * Change shipping address flag
         */
        if ($this->getRequest()->getPost('reset_shipping')) {
            $this->_getOrderCreateModel()->resetShippingMethod(true);
        }

        /**
         * Collecting shipping rates
         */
        if ($this->getRequest()->getPost('collect_shipping_rates')) {
            $this->_getOrderCreateModel()->collectShippingRates();
        }

        /**
         * Flag for using billing address for shipping
         */
        $syncFlag = $this->getRequest()->getPost('shipping_as_billing');
        if (!is_null($syncFlag)) {
            $this->_getOrderCreateModel()->setShippingAsBilling((int)$syncFlag);
        }
        
        /**
         * Applu mass changes from sidebar
         */
        if ($data = $this->getRequest()->getPost('sidebar')) {
            $this->_getOrderCreateModel()->applySidebarData($data);
        }

        /**
         * Adding product to quote from shoping cart, wishlist etc.
         */
        if ($productId = (int) $this->getRequest()->getPost('add_product')) {
            $this->_getOrderCreateModel()->addProduct($productId);
        }

        /**
         * Adding products to quote from special grid
         */
        if ($data = $this->getRequest()->getPost('add_products')) {
            $this->_getOrderCreateModel()->addProducts(Zend_Json::decode($data));
        }

        /**
         * Update quote items
         */
        if ($this->getRequest()->getPost('update_items')) {
            $items = $this->getRequest()->getPost('item', array());
            $this->_getOrderCreateModel()->updateQuoteItems($items);
        }

        /**
         * Remove quote item
         */
        if ( ($itemId = (int) $this->getRequest()->getPost('remove_item'))
             && ($from = (string) $this->getRequest()->getPost('from'))) {
            $this->_getOrderCreateModel($itemId)->removeItem($itemId, $from);
        }

        /**
         * Moove quote item
         */
        if ( ($itemId = (int) $this->getRequest()->getPost('move_item'))
            && ($moveTo = (string) $this->getRequest()->getPost('to')) ) {
            $this->_getOrderCreateModel()->moveQuoteItem($itemId, $moveTo);
        }

        if ($paymentData = $this->getRequest()->getPost('payment')) {
            $this->_getOrderCreateModel()->setPaymentData($paymentData);
        }

        $this->_getOrderCreateModel()
            ->initRuleData()
            ->saveQuote();
        return $this;
    }

    /**
     * Index page
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('left')->setIsCollapsed(true);

        $this->_initSession()
            ->_setActiveMenu('sales/order')
            ->_addContent($this->getLayout()->createBlock('adminhtml/sales_order_create'))
            ->_addJs($this->getLayout()->createBlock('core/template')->setTemplate(
                'sales/order/create/js.phtml'
            ))
            ->renderLayout();
    }

    /**
     * Loading page block
     */
    public function loadBlockAction()
    {
        try {
            $this->_initSession()
                ->_processData();
        }
        catch (Mage_Core_Exception $e){
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e){
            $this->_getSession()->addException($e, __('Processing data problem'));
        }


        $asJson= $this->getRequest()->getParam('json');
        $block = $this->getRequest()->getParam('block');
        $res = array();

        if ($block) {
            $blocks = explode(',', $block);

            if ($asJson && !in_array('messages', $blocks)) {
                $blocks[] = 'messages';
            }

            foreach ($blocks as $block) {
                $blockName = 'adminhtml/sales_order_create_'.$block;
                try {
                    $blockObject = $this->getLayout()->createBlock($blockName);
                    $res[$block] = $blockObject->toHtml();
                }
                catch (Exception $e){
                    $res[$block] = __('Can not create block "%s"', $blockName);
                }
            }
        }

        if ($asJson) {
            $this->getResponse()->setBody(Zend_Json::encode($res));
        }
        else {
            $this->getResponse()->setBody(implode('', $res));
        }
    }

    /**
     * Start order create action
     */
    public function startAction()
    {
        $this->_getSession()->clear();
        $this->_redirect('*/*', array('customer_id' => $this->getRequest()->getParam('customer_id')));
    }

    /**
     * Cancel order create
     */
    public function cancelAction()
    {
        $this->_getSession()->clear();
        $this->_redirect('*/*');
    }

    /**
     * Saving quote and create order
     */
    public function saveAction()
    {
        try {
            $order = $this->_getOrderCreateModel()
                ->importPostData($this->getRequest()->getPost('order'))
                ->createOrder();
            $this->_getSession()->clear();
            $url = $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
        catch (Mage_Core_Exception $e){
            $this->_getSession()->addError($e->getMessage());
            $url = $this->_redirect('*/*/');
        }
        catch (Exception $e){
            $this->_getSession()->addException($e, __('Order saving error: %s', $e->getMessage()));
            $url = $this->_redirect('*/*/');
        }
    }
}