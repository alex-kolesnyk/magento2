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
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Multishipping billing information
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Checkout_Block_Multishipping_Billing extends Mage_Checkout_Block_Multishipping_Abstract
{
    protected function _initChildren()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle(__('Billing Information') . ' - ' . $headBlock->getDefaultTitle());
        }
        return parent::_initChildren();
    }

    public function getAddress()
    {
        $address = $this->getData('address');
        if (is_null($address)) {
            $address = $this->getCheckout()->getQuote()->getBillingAddress();
            $this->setData('address', $address);
        }
        return $address;
    }

    public function getPaymentMethods()
    {
        $methods = Mage::getStoreConfig('payment');

        $listBlock = $this->getLayout()->createBlock('core/text_list');
        $payment = $this->getCheckout()->getQuote()->getPayment();
        if (!$payment->getCcOwner()) {
            if ($address = $this->getAddress()) {
                $payment->setCcOwner($address->getFirstname() . ' ' . $address->getLastname());
            }
        }
        foreach ($methods as $methodConfig) {
            $methodName = $methodConfig->getName();
            $className = $methodConfig->getClassName();
            $method = Mage::getModel($className)
                ->setPayment($payment);

            $methodBlock = $method->createFormBlock('checkout.payment.methods.'.$methodName);
            if (!empty($methodBlock)) {
                $listBlock->append($methodBlock);
            }
        }
        return $listBlock->toHtml();
    }

    public function getSelectAddressUrl()
    {
        return $this->getUrl('*/multishipping_address/selectBilling');
    }

    public function getPostActionUrl()
    {
        return $this->getUrl('*/*/billingPost');
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/backtoshipping');
    }
}
