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
 * Adminhtml sales orders controller
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Michael Bessolov <michael@varien.com>
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init layout, menu and breadcrumb
     *
     * @return Mage_Adminhtml_Sales_OrderController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb($this->_getHelper()->__('Sales'), $this->_getHelper()->__('Sales'))
            ->_addBreadcrumb($this->_getHelper()->__('Orders'),$this->_getHelper()-> __('Orders'))
        ;
        return $this;
    }
    
    /**
     * Orders grid
     */
    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('adminhtml/sales_order'))
            ->renderLayout();
    }
    
    /**
     * View order detale
     */
    public function viewAction()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if ($order->getId()) {
            Mage::register('sales_order', $order);
            
            $this->_initAction()
                ->_addBreadcrumb($this->_getHelper()->__('View Order'), $this->_getHelper()->__('View Order'))
                ->_addContent($this->getLayout()->createBlock('adminhtml/sales_order_view'))
                ->renderLayout();
        }
        else {
            $this->_getSession()->addError($this->_getHelper()->__('This order no longer exists'));
            $this->_redirect('*/*/');
        }
    }
    
    public function cancelAction()
    {
        
    }
    
    public function changeStatusAction()
    {
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**
     * Delete (cancel) order action
     */
    public function deleteAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

        if ($order->getId()) {
            $order->cancel();
            try {
                $order->save();
                $this->_getSession()->addSuccess($this->_getHelper()->__('Order was successfully cancelled'));
            } 
            catch (Mage_Core_Exception $e){
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->_getHelper()->__('Order was not cancelled'));
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $orderId));            
        }
        else {
            $this->_getSession()->addError($this->_getHelper()->__('This order no longer exists'));
            $this->_redirect('*/*/');
        }
    }
    
    /**
     * Edit order status
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('order_id');
        $model = Mage::getModel('sales/order')->load($id);

        if ($model->getId()) {
            Mage::register('sales_order', $model);
    
            $this->_initAction()
                ->_addBreadcrumb(__('Edit Order'), __('Edit Order'))
                ->_addContent($this->getLayout()->createBlock('adminhtml/sales_order_edit'))
                ->renderLayout();
        }
        else {
            $this->_getSession()->addError($this->_getHelper()->__('This order no longer exists'));
            $this->_redirect('*/*/');
        }
    }
    
    /**
     * Save order
     */
    public function saveAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

        if ($order->getId()) {
            if ($newStatus = $this->getRequest()->getParam('new_status')) {
                $notifyCustomer = $this->getRequest()->getParam('notify_customer', false);
                $comment = $this->getRequest()->getParam('comments', '');
                
                $order->addStatus($newStatus, $comment, $notifyCustomer);
                
                try {
                    $order->save();
                    if ($notifyCustomer) {
                        $order->sendOrderUpdateEmail($comment);
                    }
                    $this->_getSession()->addSuccess($this->_getHelper()->__('Order status was successfully changed'));
                } 
                catch (Mage_Core_Exception $e){
                    $this->_getSession()->addError($e->getMessage());
                }
                catch (Exception $e) {
                    $this->_getSession()->addError($this->_getHelper()->__('Order was not changed'));
                }
            }
    
            $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
        }
        else {
            Mage::getSingleton('adminhtml/session')->addError(__('This order no longer exists'));
            $this->_redirect('*/*/');
        }
    }
    
    /**
     * Random orders generation
     */
    /*public function generateAction()
    {
        $count = (int) $this->getRequest()->getParam('count', 10);
        if ($count && $count>100) {
            $count = 100;
        }
        
        for ($i=0; $i<$count; $i++){
            $randomOrder = Mage::getModel('adminhtml/sales_order_random')
                ->render()
                ->save();
        }
    }*/

    protected function _isAllowed()
    {
	    return Mage::getSingleton('admin/session')->isAllowed('sales/order');
    }
}
