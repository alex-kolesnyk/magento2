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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * XmlConnect customer controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_XmlConnect_CustomerController extends Mage_XmlConnect_Controller_Action
{
    /**
     * Get customer session model
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Customer authentification action
     */
    public function loginAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_message($this->__('You are already logged in.'), self::MESSAGE_STATUS_ERROR);
            return;
        }

        if ($this->getRequest()->getPost()) {
            $user = $this->getRequest()->getParam('username');
            $pass = $this->getRequest()->getParam('password');
            try {
                if ($this->_getSession()->login($user, $pass)) {
                    $this->_message($this->__('Authentification complete.'), self::MESSAGE_STATUS_SUCCESS);
                } else {
                    $this->_message($this->__('Invalid login or password.'), self::MESSAGE_STATUS_ERROR);
                }
            } catch (Mage_Core_Exception $e) {
                switch ($e->getCode()) {
                    case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                        // TODO: resend configmation email message with action
                        break;
                    case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                        $message = $e->getMessage();
                        break;
                    default:
                        $message = $e->getMessage();
                }
                $this->_message($message, self::MESSAGE_STATUS_ERROR);
            } catch (Exception $e) {
                $this->_message($this->__('Customer authentification problem.'), self::MESSAGE_STATUS_ERROR);
            }
        } else {
            $this->_message($this->__('Login and password are required.'), self::MESSAGE_STATUS_ERROR);
        }
    }

    /**
     * Customer logout
     *
     */
    public function logoutAction()
    {
        try {
            if ($this->_getSession()->isLoggedIn()) {
                $this->_getSession()->logout();
                $this->_message($this->__('Logout complete.'), self::MESSAGE_STATUS_SUCCESS);
            }
            else {
                $this->_message($this->__('Customer not loggined.'), self::MESSAGE_STATUS_ERROR);
            }
        }
        catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        }
        catch (Exception $e) {
            $this->_message($this->__('Customer logout problem.'), self::MESSAGE_STATUS_ERROR);
        }
    }

    /**
     * Customer registration form
     */
    public function formAction()
    {
//        $this->loadLayout(false);
//        $this->renderLayout();
        $xml = <<<EOT
<?xml version="1.0"?>
<form name="account_form" method="post">
    <fieldset>
        <field name="firstname" type="text" label="First Name" required="true">
            <validators>
                <validator type="regexp" message="Letters only">/^[a-z]/i</validator>
            </validators>
        </field>
        <field name="lastname" type="text" label="Last Name" required="true">
            <validators>
                <validator type="regexp" message="Letters only">/^[a-z]/i</validator>
            </validators>
        </field>
        <field name="email" type="text" label="Email" required="true">
            <validators>
                <validator type="email" message="Wrong email format"/>
            </validators>
        </field>
        <field name="password" type="password" label="Password" required="true"/>
        <field name="password_confirm" type="password" label="Confirm" required="true">
            <validators>
                <validator type="confirmation" message="....">password</validator>
            </validators>
        </field>
    </fieldset>
    <fieldset legend="Receive Email Notifications">
        <field name="newslatter" type="checkbox" label="Promos and News"/>
    </fieldset>
</form>
EOT;
        $this->getResponse()->setBody($xml);
    }

    /**
     * Save customer account
     */
    public function saveAction()
    {

    }

    /**
     * Create customer address form
     */
    public function addressFormAction()
    {

    }

    /**
     * Save customer address
     */
    public function addressSaveAction()
    {

    }

    /**
     * Send new password to customer by specified email
     */
    public function forgotPasswordAction()
    {
        $email = $this->getRequest()->getPost('email');
        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->_message($this->__('Invalid email address.'), self::MESSAGE_STATUS_ERROR);
                return;
            }
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

            if ($customer->getId()) {
                try {
                    $newPassword = $customer->generatePassword();
                    $customer->changePassword($newPassword, false);
                    $customer->sendPasswordReminderEmail();
                    $this->_message($this->__('A new password has been sent.'), self::MESSAGE_STATUS_SUCCESS);

                    return;
                }
                catch (Mage_Core_Exception $e) {
                    $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
                }
                catch (Exception $e) {
                    $this->_message($this->__('Sending/Changing new password problem.'), self::MESSAGE_STATUS_ERROR);
                }
            }
            else {
                $this->_message($this->__('This email address was not found in our records.'), self::MESSAGE_STATUS_ERROR);
            }
        }
        else {
            $this->_message($this->__('Customer email not specified.'), self::MESSAGE_STATUS_ERROR);
        }
    }
}
