<?php
/**
 * Adminhtml sales orders controller
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Michael Bessolov <michael@varien.com>
 */
class Mage_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Enter description here...
     *
     * @return Mage_Adminhtml_Sales_OrderController
     */
    protected function _initAction()
    {
        $this->loadLayout('baseframe')
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb(__('Sales'), __('Sales'))
            ->_addBreadcrumb(__('Orders'), __('Orders'))
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('adminhtml/sales_order'))
            ->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('order_id');
        $model = Mage::getModel('sales/order');

        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(__('This order no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(__('This order no longer exists'));
            $this->_redirect('*/*/');
            return;
        }

        Mage::register('sales_order', $model);

        $this->_initAction()
            ->_addBreadcrumb(__('Edit Order'), __('Edit Order'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/sales_order_edit'))
            ->renderLayout();
    }

    public function viewAction()
    {
        $id = $this->getRequest()->getParam('order_id');
        $model = Mage::getModel('sales/order');

        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(__('This order no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(__('This order no longer exists'));
            $this->_redirect('*/*/');
            return;
        }

        Mage::register('sales_order', $model);

        $this->_initAction()
            ->_addBreadcrumb(__('View Order'), __('View Order'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/sales_order_view'))
            ->renderLayout();
    }

    public function deleteAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order');

        if ($orderId) {
            $order->load($orderId);
            if (! $order->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(__('This order no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(__('This order no longer exists'));
            $this->_redirect('*/*/');
            return;
        }

        try {
            $order->cancel()->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(__('Order was cancelled successfully'));
            $this->_redirect('*/*/');
            return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(__('Order was not cancelled'));
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/');
            return;
        }

    }

    public function saveAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order');
        /* @var $order Mage_Sales_Model_Order */

        if ($orderId) {
            $order->load($orderId);
            if (! $order->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(__('This order no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(__('This order no longer exists'));
            $this->_redirect('*/*/');
            return;
        }

        if ($newStatus = $this->getRequest()->getParam('new_status')) {
            $notifyCustomer = $this->getRequest()->getParam('notify_customer', false);
            try {
                $order->addStatus($newStatus, $this->getRequest()->getParam('comments', ''), $notifyCustomer);
                if ($notifyCustomer) {
                    $order->sendOrderUpdateEmail();
                }

                $order->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(__('Order status was changed successfully'));
                $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(__('Order was not changed'));
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
                return;
            }
        }

        $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
    }

}
