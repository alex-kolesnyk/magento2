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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Customer
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer module observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Customer_Model_Observer
{
    /**
     * VAT ID validation processed flag code
     */
    const VIV_PROCESSED_FLAG = 'viv_after_address_save_processed';

    /**
     * VAT ID validation currently saved address flag
     */
    const VIV_CURRENTLY_SAVED_ADDRESS = 'currently_saved_address';

    /**
     * Check whether specified billing address is default for its customer
     *
     * @param Mage_Customer_Model_Address $address
     * @return bool
     */
    protected function _isDefaultBilling($address)
    {
        return $address->getId() && $address->getId() == $address->getCustomer()->getDefaultBilling();
    }

    /**
     * Check whether specified address should be processed in after_save event handler
     *
     * @param Mage_Customer_Model_Address $address
     * @return bool
     */
    protected function _canProcessAddress($address)
    {
        if ($address->getForceProcess()) {
            return true;
        }

        if (Mage::registry(self::VIV_CURRENTLY_SAVED_ADDRESS) != $address->getId()) {
            return false;
        }

        return $this->_isDefaultBilling($address);
    }

    /**
     * Before load layout event handler
     *
     * @param Varien_Event_Observer $observer
     */
    public function beforeLoadLayout($observer)
    {
        $loggedIn = Mage::getSingleton('customer/session')->isLoggedIn();

        $observer->getEvent()->getLayout()->getUpdate()
           ->addHandle('customer_logged_' . ($loggedIn ? 'in' : 'out'));
    }

    /**
     * Address before save event handler
     *
     * @param Varien_Event_Observer $observer
     */
    public function beforeAddressSave($observer)
    {
        if (Mage::registry(self::VIV_CURRENTLY_SAVED_ADDRESS)) {
            Mage::unregister(self::VIV_CURRENTLY_SAVED_ADDRESS);
        }

        /** @var $customerAddress Mage_Customer_Model_Address */
        $customerAddress = $observer->getCustomerAddress();
        if ($customerAddress->getId()) {
            Mage::register(self::VIV_CURRENTLY_SAVED_ADDRESS, $customerAddress->getId());
        } elseif ($customerAddress->getIsDefaultBilling()) {
            $customerAddress->setForceProcess(true);
        }
        else {
            Mage::register(self::VIV_CURRENTLY_SAVED_ADDRESS, 'new_address');
        }
    }

    /**
     * Address after save event handler
     *
     * @param Varien_Event_Observer $observer
     */
    public function afterAddressSave($observer)
    {
        /** @var $customerAddress Mage_Customer_Model_Address */
        $customerAddress = $observer->getCustomerAddress();

        if (!Mage::helper('customer/address')->isVatValidationEnabled()
            || Mage::registry(self::VIV_PROCESSED_FLAG)
            || !$this->_canProcessAddress($customerAddress)
            || $customerAddress->getVatId() == ''
        ) {
            return;
        }

        try {
            Mage::register(self::VIV_PROCESSED_FLAG, true);

            /** @var $customerHelper Mage_Customer_Helper_Data */
            $customerHelper = Mage::helper('customer');

            $result = $customerHelper->checkVatNumber(
                $customerAddress->getCountryId(),
                $customerAddress->getVatId()
            );

            $newGroupId = $customerHelper->getCustomerGroupIdBasedOnVatNumber(
                $customerAddress->getCountryId(), $result
            );

            $customer = $customerAddress->getCustomer();
            if (!$customer->getDisableAutoGroupChange() && $customer->getGroupId() != $newGroupId) {
                $customer->setGroupId($newGroupId);
                $customer->save();
            }

            if (!Mage::app()->getStore()->isAdmin()) {
                Mage::getSingleton('customer/session')->addSuccess(
                    Mage::helper('customer')->getVatValidationUserMessage($customerAddress,
                        $customer->getDisableAutoGroupChange(), $result)
                );
            }
        } catch (Exception $e) {
            Mage::register(self::VIV_PROCESSED_FLAG, false);
        }
    }

    /**
     * Assign custom renderer for VAT ID field in billing address form
     *
     * @param Varien_Event_Observer $observer
     */
    public function prepareFormAfter($observer)
    {
        /** @var $formBlock Mage_Adminhtml_Block_Sales_Order_Create_Billing_Address */
        $formBlock = $observer->getForm();
        $formBlock->getForm()->getElement('vat_id')->setRenderer(
            $formBlock->getLayout()->createBlock('adminhtml/customer_sales_order_address_form_billing_renderer_vat')
        );
    }

    /**
     * Revert emulated customer group_id
     *
     * @param Varien_Event_Observer $observer
     */
    public function quoteSubmitAfter($observer)
    {
        if (!Mage::helper('customer/address')->isVatValidationEnabled()) {
            return;
        }
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = $observer->getQuote()->getCustomer();
        $customer->setGroupId(
            $customer->getOrigData('group_id')
        );
        $customer->save();
    }
}
