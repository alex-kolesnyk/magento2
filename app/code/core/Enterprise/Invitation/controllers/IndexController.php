<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @category   Enterprise
 * @package    Enterprise_Invitation
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Invitation frontend controller
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */
class Enterprise_Invitation_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Only logged in users can use this functionality,
     * this function checks if user is logged in before all other actions
     *
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    /**
     * Send invitations from frontend
     *
     */
    public function sendAction()
    {
        if (!Mage::helper('enterprise_invitation')->isEnabled()) {
            $this->norouteAction();
            return;
        }
        $data = $this->getRequest()->getPost();
        if ($data) {
            $now = Mage::app()->getLocale()->date()
                    ->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE)
                    ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

            $customer = Mage::getSingleton('customer/session')->getCustomerId();

            if (Mage::helper('enterprise_invitation')->getUseInvitationMessage() && !empty($data['message'])) {
                $message = $data['message'];
            } else {
                $message = null;
            }

            if (Mage::helper('enterprise_invitation')->getUseInviterGroup()) {
                $customerGroup = Mage::getSingleton('customer/session')->getCustomerGroupId();
            } else {
                $customerGroup = Mage::getStoreConfig('customer/create_account/default_group');
            }

            $invPerSend = Mage::helper('enterprise_invitation')->getMaxInvitationAmountPerSend();
            $sentAmount = 0;
            $notSentCustomerExists = 0;
            foreach ($data['email'] as $email) {
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    continue;
                }
                if (Mage::getModel('customer/customer')->setWebsiteId(
                        Mage::app()->getWebsite()->getId()
                    )->loadByEmail($email)->getId()) {
                    $notSentCustomerExists++;
                    continue;
                }
                try {
                    if ($sentAmount >= $invPerSend) {
                        // If invatation amount left, stop sending.
                        break;
                    }
                    // save invitation into db
                    $invitation = Mage::getModel('enterprise_invitation/invitation');
                    $code = $invitation->generateCode();
                    $invitationData = array(
                        'email' => $email,
                        'date' => $now,
                        'customer_id' => $customer,
                        'group_id' => $customerGroup,
                        'protection_code' => $code,
                        'store_id' => Mage::app()->getStore()->getId(),
                        'message' => $message,
                        'status' => Enterprise_Invitation_Model_Invitation::STATUS_SENT
                    );
                    $invitation->setData($invitationData)->save();

                    $invitation->sendInvitationEmail();

                    Mage::getSingleton('customer/session')->addSuccess(Mage::helper('enterprise_invitation')->__('Invitation for %s has been sent successfully.', $email));
                    $sentAmount ++;
                } catch (Mage_Core_Exception $e) {
                    Mage::getSingleton('customer/session')->addError($e->getMessage());
                } catch (Exception $e) {
                    Mage::getSingleton('customer/session')->addError(Mage::helper('enterprise_invitation')->__('Email to %s was not sent for some reason. Please try again later.', $email));
                }
            }

            if ($notSentCustomerExists > 0) {
                Mage::getSingleton('customer/session')->addNotice(
                    Mage::helper('enterprise_invitation')->__('Invitations not sent to %d email(s) because accounts exist for them.', $notSentCustomerExists)
                );
            }

            $this->_redirect('*/*/');
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->loadLayoutUpdates();
        $this->renderLayout();

    }


    /**
     * View invitation list in 'My Account' section
     *
     **/
    public function indexAction()
    {
        if (!Mage::helper('enterprise_invitation')->isEnabled()) {
            $this->norouteAction();
            return;
        }
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->loadLayoutUpdates();
        $this->renderLayout();
    }
}
