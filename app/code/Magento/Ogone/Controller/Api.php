<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Ogone
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Ogone Api Controller
 */
class Magento_Ogone_Controller_Api extends Magento_Core_Controller_Front_Action
{
    /**
     * Order instance
     */
    protected $_order;

    /**
     * Get checkout session namespace
     *
     * @return Magento_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('Magento_Checkout_Model_Session');
    }

    /**
     * Get singleton with Checkout by Ogone Api
     *
     * @return Magento_Ogone_Model_Api
     */
    protected function _getApi()
    {
        return Mage::getSingleton('Magento_Ogone_Model_Api');
    }

    /**
     * Return order instance loaded by increment id'
     *
     * @return Magento_Sales_Model_Order
     */
    protected function _getOrder()
    {
        if (empty($this->_order)) {
            $orderId = $this->getRequest()->getParam('orderID');
            $this->_order = Mage::getModel('Magento_Sales_Model_Order');
            $this->_order->loadByIncrementId($orderId);
        }
        return $this->_order;
    }

    /**
     * Validation of incoming Ogone data
     *
     * @return bool
     */
    protected function _validateOgoneData()
    {
        $params = $this->getRequest()->getParams();
        $api = $this->_getApi();
        $api->debugData(array('result' => $params));

        $hashValidationResult = false;
        if ($api->getConfig()->getShaInCode()) {
            $referenceHash = $api->getHash(
                $params,
                $api->getConfig()->getShaInCode(),
                Magento_Ogone_Model_Api::HASH_DIR_IN,
                (int)$api->getConfig()->getConfigData('shamode'),
                $api->getConfig()->getConfigData('hashing_algorithm')
            );
            if ($params['SHASIGN'] == $referenceHash) {
                $hashValidationResult = true;
            }
        }

        if (!$hashValidationResult) {
            $this->_getCheckout()->addError($this->__('The hash is not valid.'));
            return false;
        }

        $order = $this->_getOrder();
        if (!$order->getId()){
            $this->_getCheckout()->addError($this->__('The order is not valid.'));
            return false;
        }

        return true;
    }

    /**
     * Load place from layout to make POST on ogone
     */
    public function placeformAction()
    {
        $lastIncrementId = $this->_getCheckout()->getLastRealOrderId();
        if ($lastIncrementId) {
            $order = Mage::getModel('Magento_Sales_Model_Order');
            $order->loadByIncrementId($lastIncrementId);
            if ($order->getId()) {
                $order->setState(
                    Magento_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Magento_Ogone_Model_Api::PENDING_OGONE_STATUS,
                    Mage::helper('Magento_Ogone_Helper_Data')->__('Start Ogone Processing')
                );
                $order->save();

                $this->_getApi()->debugOrder($order);
            }
        }

        $this->_getCheckout()->getQuote()->setIsActive(false)->save();
        $this->_getCheckout()->setOgoneQuoteId($this->_getCheckout()->getQuoteId());
        $this->_getCheckout()->setOgoneLastSuccessQuoteId($this->_getCheckout()->getLastSuccessQuoteId());
        $this->_getCheckout()->clear();

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Display our pay page, need to ogone payment with external pay page mode
     */
    public function paypageAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Action to control postback data from ogone
     *
     */
    public function postBackAction()
    {
        if (!$this->_validateOgoneData()) {
            $this->getResponse()->setHeader("Status", "404 Not Found");
            return false;
        }

        $this->_ogoneProcess();
    }

    /**
     * Action to process ogone offline data
     *
     */
    public function offlineProcessAction()
    {
        if (!$this->_validateOgoneData()) {
            $this->getResponse()->setHeader("Status","404 Not Found");
            return false;
        }
        $this->_ogoneProcess();
    }

    /**
     * Made offline ogone data processing, depending of incoming statuses
     */
    protected function _ogoneProcess()
    {
        $status = $this->getRequest()->getParam('STATUS');
        switch ($status) {
            case Magento_Ogone_Model_Api::OGONE_AUTHORIZED :
            case Magento_Ogone_Model_Api::OGONE_AUTH_PROCESSING:
            case Magento_Ogone_Model_Api::OGONE_PAYMENT_REQUESTED_STATUS :
                $this->_acceptProcess();
                break;
            case Magento_Ogone_Model_Api::OGONE_AUTH_REFUZED:
            case Magento_Ogone_Model_Api::OGONE_PAYMENT_INCOMPLETE:
            case Magento_Ogone_Model_Api::OGONE_TECH_PROBLEM:
                $this->_declineProcess();
                break;
            case Magento_Ogone_Model_Api::OGONE_AUTH_UKNKOWN_STATUS:
            case Magento_Ogone_Model_Api::OGONE_PAYMENT_UNCERTAIN_STATUS:
                $this->_exceptionProcess();
                break;
            default:
                //all unknown transaction will accept as exceptional
                $this->_exceptionProcess();
        }
    }

    /**
     * when payment gateway accept the payment, it will land to here
     * need to change order status as processed ogone
     * update transaction id
     *
     */
    public function acceptAction()
    {
        if (!$this->_validateOgoneData()) {
            $this->_redirect('checkout/cart');
            return;
        }
        $this->_ogoneProcess();
    }

    /**
     * Process success action by accept url
     */
    protected function _acceptProcess()
    {
        $params = $this->getRequest()->getParams();
        $order = $this->_getOrder();
        if (!$this->_isOrderValid($order)) {
            return;
        }

        $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());

        $this->_prepareCCInfo($order, $params);
        $order->getPayment()->setTransactionId($params['PAYID']);
        $order->getPayment()->setLastTransId($params['PAYID']);

        try {
            $status = $this->getRequest()->getParam('STATUS');
            switch ($status) {
                case Magento_Ogone_Model_Api::OGONE_AUTHORIZED:
                case Magento_Ogone_Model_Api::OGONE_AUTH_PROCESSING:
                    $this->_processAuthorize();
                    break;
                case Magento_Ogone_Model_Api::OGONE_PAYMENT_REQUESTED_STATUS:
                    $this->_processDirectSale();
                    break;
                default:
                    throw new Exception (Mage::helper('Magento_Ogone_Helper_Data')->__('Can\'t detect Ogone payment action'));
             }
        } catch(Exception $e) {
            $this->_getCheckout()->addError(Mage::helper('Magento_Ogone_Helper_Data')->__('The order cannot be saved.'));
            $this->_redirect('checkout/cart');
            return;
        }
    }

    /**
     * Process Configured Payment Action: Direct Sale, create invoce if state is Pending
     *
     */
    protected function _processDirectSale()
    {
        $order = $this->_getOrder();
        $status = $this->getRequest()->getParam('STATUS');
        try{
            if ($status ==  Magento_Ogone_Model_Api::OGONE_AUTH_PROCESSING) {
                $order->setState(
                    Magento_Sales_Model_Order::STATE_PROCESSING,
                    Magento_Ogone_Model_Api::WAITING_AUTHORIZATION,
                    Mage::helper('Magento_Ogone_Helper_Data')->__('Authorization Waiting from Ogone')
                );
                $order->save();
            }elseif ($order->getState()==Magento_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                if ($status ==  Magento_Ogone_Model_Api::OGONE_AUTHORIZED) {
                    if ($order->getStatus() != Magento_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                        $order->setState(
                            Magento_Sales_Model_Order::STATE_PROCESSING,
                            Magento_Ogone_Model_Api::PROCESSING_OGONE_STATUS,
                            Mage::helper('Magento_Ogone_Helper_Data')->__('Processed by Ogone')
                        );
                    }
                } else {
                    $order->setState(
                        Magento_Sales_Model_Order::STATE_PROCESSING,
                        Magento_Ogone_Model_Api::PROCESSED_OGONE_STATUS,
                        Mage::helper('Magento_Ogone_Helper_Data')->__('Processed by Ogone')
                    );
                }

                if (!$order->getInvoiceCollection()->getSize()) {
                    $invoice = $order->prepareInvoice();
                    $invoice->register();
                    $invoice->setState(Magento_Sales_Model_Order_Invoice::STATE_PAID);
                    $invoice->getOrder()->setIsInProcess(true);

                    $transactionSave = Mage::getModel('Magento_Core_Model_Resource_Transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder())
                        ->save();
                    $order->sendNewOrderEmail();
                }
            } else {
                $order->save();
            }
            $this->_redirect('checkout/onepage/success');
            return;
        } catch (Exception $e) {
            $this->_getCheckout()->addError(Mage::helper('Magento_Ogone_Helper_Data')->__('Order can\'t save'));
            $this->_redirect('checkout/cart');
            return;
        }
    }

    /**
     * Process Configured Payment Actions: Authorized, Default operation
     * just place order
     */
    protected function _processAuthorize()
    {
        $order = $this->_getOrder();
        $status = $this->getRequest()->getParam('STATUS');
        try {
            if ($status ==  Magento_Ogone_Model_Api::OGONE_AUTH_PROCESSING) {
                $order->setState(
                    Magento_Sales_Model_Order::STATE_PROCESSING,
                    Magento_Ogone_Model_Api::WAITING_AUTHORIZATION,
                    Mage::helper('Magento_Ogone_Helper_Data')->__('Authorization Waiting from Ogone')
                );
            } else {
                //to send new order email only when state is pending payment
                if ($order->getState()==Magento_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                    $order->sendNewOrderEmail();
                }
                $order->setState(
                    Magento_Sales_Model_Order::STATE_PROCESSING,
                    Magento_Ogone_Model_Api::PROCESSED_OGONE_STATUS,
                    Mage::helper('Magento_Ogone_Helper_Data')->__('Processed by Ogone')
                );
            }
            $order->save();
            $this->_redirect('checkout/onepage/success');
            return;
        } catch(Exception $e) {
            $this->_getCheckout()->addError(Mage::helper('Magento_Ogone_Helper_Data')->__('Order can\'t save'));
            $this->_redirect('checkout/cart');
            return;
        }
    }

    /**
     * We get some CC info from ogone, so we must save it
     *
     * @param Magento_Sales_Model_Order $order
     * @param array $ccInfo
     *
     * @return Magento_Ogone_Controller_Api
     */
    protected function _prepareCCInfo($order, $ccInfo)
    {
        $order->getPayment()->setCcOwner($ccInfo['CN']);
        $order->getPayment()->setCcNumberEnc($ccInfo['CARDNO']);
        $order->getPayment()->setCcLast4(substr($ccInfo['CARDNO'], -4));
        $order->getPayment()->setCcExpMonth(substr($ccInfo['ED'], 0, 2));
        $order->getPayment()->setCcExpYear(substr($ccInfo['ED'], 2, 2));
        return $this;
    }


    /**
     * the payment result is uncertain
     * exception status can be 52 or 92
     * need to change order status as processing ogone
     * update transaction id
     *
     */
    public function exceptionAction()
    {
        if (!$this->_validateOgoneData()) {
            $this->_redirect('checkout/cart');
            return;
        }
        $this->_exceptionProcess();
    }

    /**
     * Process exception action by ogone exception url
     */
    public function _exceptionProcess()
    {
        $params = $this->getRequest()->getParams();
        $order = $this->_getOrder();
        if (!$this->_isOrderValid($order)) {
            return;
        }

        $exception = '';
        switch($params['STATUS']) {
            case Magento_Ogone_Model_Api::OGONE_PAYMENT_UNCERTAIN_STATUS :
                $exception = Mage::helper('Magento_Ogone_Helper_Data')->__('Something went wrong during the payment process, and so the result is unpredictable.');
                break;
            case Magento_Ogone_Model_Api::OGONE_AUTH_UKNKOWN_STATUS :
                $exception = Mage::helper('Magento_Ogone_Helper_Data')->__('Something went wrong during the authorization process, and so the result is unpredictable.');
                break;
            default:
                $exception = Mage::helper('Magento_Ogone_Helper_Data')->__('Unknown exception');
        }

        if (!empty($exception)) {
            try{
                $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());
                $this->_prepareCCInfo($order, $params);
                $order->getPayment()->setLastTransId($params['PAYID']);
                //to send new order email only when state is pending payment
                if ($order->getState()==Magento_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                    $order->sendNewOrderEmail();
                    $order->setState(
                        Magento_Sales_Model_Order::STATE_PROCESSING, Magento_Ogone_Model_Api::PROCESSING_OGONE_STATUS,
                        $exception
                    );
                } else {
                    $order->addStatusToHistory(Magento_Ogone_Model_Api::PROCESSING_OGONE_STATUS, $exception);
                }
                $order->save();
            }catch(Exception $e) {
                $this->_getCheckout()->addError(Mage::helper('Magento_Ogone_Helper_Data')->__('Something went wrong while saving this order.'));
            }
        } else {
            $this->_getCheckout()->addError(Mage::helper('Magento_Ogone_Helper_Data')->__('Exception not defined'));
        }

        $this->_redirect('checkout/onepage/success');
    }

    /**
     * when payment got decline
     * need to change order status to cancelled
     * take the user back to shopping cart
     *
     */
    public function declineAction()
    {
        if (!$this->_validateOgoneData()) {
            $this->_redirect('checkout/cart');
            return;
        }
        $this->_getCheckout()->setQuoteId($this->_getCheckout()->getOgoneQuoteId());
        $this->_declineProcess();
        return $this;
    }

    /**
     * Process decline action by ogone decline url
     */
    protected function _declineProcess()
    {
        $status     = Magento_Ogone_Model_Api::DECLINE_OGONE_STATUS;
        $comment    = Mage::helper('Magento_Ogone_Helper_Data')->__('Declined Order on Ogone side');
        $this->_getCheckout()->addError(Mage::helper('Magento_Ogone_Helper_Data')->__('The payment transaction has been declined.'));
        $this->_cancelOrder($status, $comment);
    }

    /**
     * when user cancel the payment
     * change order status to cancelled
     * need to rediect user to shopping cart
     *
     * @return Magento_Ogone_Controller_Api
     */
    public function cancelAction()
    {
        if (!$this->_validateOgoneData()) {
            $this->_redirect('checkout/cart');
            return;
        }
        $this->_getCheckout()->setQuoteId($this->_getCheckout()->getOgoneQuoteId());
        $this->_cancelProcess();
        return $this;
    }

    /**
     * Process cancel action by cancel url
     *
     * @return Magento_Ogone_Controller_Api
     */
    public function _cancelProcess()
    {
        $status     = Magento_Ogone_Model_Api::CANCEL_OGONE_STATUS;
        $comment    = Mage::helper('Magento_Ogone_Helper_Data')->__('The order was canceled on the Ogone side.');
        $this->_cancelOrder($status, $comment);
        return $this;
    }

    /**
     * Cancel action, used for decline and cancel processes
     *
     * @return Magento_Ogone_Controller_Api
     */
    protected function _cancelOrder($status, $comment='')
    {
        $order = $this->_getOrder();
        if (!$this->_isOrderValid($order)) {
            return;
        }

        try{
            $order->cancel();
            $order->setState(Magento_Sales_Model_Order::STATE_CANCELED, $status, $comment);
            $order->save();
        }catch(Exception $e) {
            $this->_getCheckout()->addError(Mage::helper('Magento_Ogone_Helper_Data')->__('Something went wrong while canceling this order.'));
        }

        $this->_redirect('checkout/cart');
        return $this;
    }

    /**
     * Check order payment method
     *
     * @param Magento_Sales_Model_Order $order
     * @return bool
     */
    protected function _isOrderValid($order)
    {
        return Magento_Ogone_Model_Api::PAYMENT_CODE == $order->getPayment()->getMethodInstance()->getCode();
    }
}
