<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Adminhtml shipment create
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Adminhtml_Block_Sales_Order_Shipment_View extends Magento_Adminhtml_Block_Widget_Form_Container
{

    protected function _construct()
    {
        $this->_objectId    = 'shipment_id';
        $this->_controller  = 'sales_order_shipment';
        $this->_mode        = 'view';

        parent::_construct();

        $this->_removeButton('reset');
        $this->_removeButton('delete');
        if (!$this->getShipment()) {
            return;
        }

        if ($this->_authorization->isAllowed('Magento_Sales::emails')) {
            $this->_updateButton('save', 'label', Mage::helper('Magento_Sales_Helper_Data')->__('Send Tracking Information'));
            $this->_updateButton('save',
                'onclick', "deleteConfirm('"
                . Mage::helper('Magento_Sales_Helper_Data')->__('Are you sure you want to send a Shipment email to customer?')
                . "', '" . $this->getEmailUrl() . "')"
            );
        }

        if ($this->getShipment()->getId()) {
            $this->_addButton('print', array(
                'label'     => Mage::helper('Magento_Sales_Helper_Data')->__('Print'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getPrintUrl().'\')'
                )
            );
        }
    }

    /**
     * Retrieve shipment model instance
     *
     * @return Magento_Sales_Model_Order_Shipment
     */
    public function getShipment()
    {
        return Mage::registry('current_shipment');
    }

    public function getHeaderText()
    {
        if ($this->getShipment()->getEmailSent()) {
            $emailSent = Mage::helper('Magento_Sales_Helper_Data')->__('the shipment email was sent');
        }
        else {
            $emailSent = Mage::helper('Magento_Sales_Helper_Data')->__('the shipment email is not sent');
        }
        return Mage::helper('Magento_Sales_Helper_Data')->__('Shipment #%1$s | %3$s (%2$s)', $this->getShipment()->getIncrementId(), $emailSent, $this->formatDate($this->getShipment()->getCreatedAtDate(), 'medium', true));
    }

    public function getBackUrl()
    {
        return $this->getUrl(
            '*/sales_order/view',
            array(
                'order_id'  => $this->getShipment() ? $this->getShipment()->getOrderId() : null,
                'active_tab'=> 'order_shipments'
            ));
    }

    public function getEmailUrl()
    {
        return $this->getUrl('*/sales_order_shipment/email', array('shipment_id'  => $this->getShipment()->getId()));
    }

    public function getPrintUrl()
    {
        return $this->getUrl('*/*/print', array(
            'invoice_id' => $this->getShipment()->getId()
        ));
    }

    public function updateBackButtonUrl($flag)
    {
        if ($flag) {
            if ($this->getShipment()->getBackUrl()) {
                return $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getShipment()->getBackUrl() . '\')');
            }
            return $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/sales_shipment/') . '\')');
        }
        return $this;
    }
}
