<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Customer
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Customer account controller
 */
class Magento_Customer_Controller_Account extends Magento_Core_Controller_Front_Action
{
    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('loginPost', 'createpost');

    /**
     * List of actions that are allowed for not authorized users
     *
     * @var array
     */
    protected $_openActions = array(
        'create',
        'login',
        'logoutsuccess',
        'forgotpassword',
        'forgotpasswordpost',
        'resetpassword',
        'resetpasswordpost',
        'confirm',
        'confirmation',
        'createpassword',
        'createpost',
        'loginpost'
    );

    /**
     * Retrieve customer session model object
     *
     * @return Magento_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('Magento_Customer_Model_Session');
    }

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        // a brute-force protection here would be nice

        parent::preDispatch();

        if (!$this->_objectManager->get('Magento_Core_Model_App_State')->isInstalled()) {
            return;
        }

        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        $action = $this->getRequest()->getActionName();
        $pattern = '/^(' . implode('|', $this->_getAllowedActions()) . ')$/i';

        if (!preg_match($pattern, $action)) {
            if (!$this->_getSession()->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
            }
        } else {
            $this->_getSession()->setNoReferer(true);
        }
    }

    /**
     * Get list of actions that are allowed for not authorized users
     *
     * @return array
     */
    protected function _getAllowedActions()
    {
        return $this->_openActions;
    }

    /**
     * Action postdispatch
     *
     * Remove No-referer flag from customer session after each action
     */
    public function postDispatch()
    {
        parent::postDispatch();
        $this->_getSession()->unsNoReferer(false);
    }

    /**
     * Default customer account page
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('Magento_Customer_Model_Session');
        $this->_initLayoutMessages('Magento_Catalog_Model_Session');
        $this->getLayout()->getBlock('head')->setTitle(__('My Account'));
        $this->renderLayout();
    }

    /**
     * Customer login form page
     */
    public function loginAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $this->getResponse()->setHeader('Login-Required', 'true');
        $this->loadLayout();
        $this->_initLayoutMessages('Magento_Customer_Model_Session');
        $this->_initLayoutMessages('Magento_Catalog_Model_Session');
        $this->renderLayout();
    }

    /**
     * Login post action
     */
    public function loginPostAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session = $this->_getSession();

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($session->getCustomer(), true);
                    }
                } catch (Magento_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Magento_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = $this->_objectManager->get('Magento_Customer_Helper_Data')
                                ->getEmailConfirmationUrl($login['username']);
                            $message = __('This account is not confirmed.'
                                    . ' <a href="%1">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Magento_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                            break;
                    }
                    $session->addError($message);
                    $session->setUsername($login['username']);
                } catch (Exception $e) {
                    // $this->_objectManager->get('Magento_Core_Model_Logger')->logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $session->addError(__('Login and password are required.'));
            }
        }

        $this->_loginPostRedirect();
    }

    /**
     * Define target URL and redirect customer after logging in
     */
    protected function _loginPostRedirect()
    {
        $session = $this->_getSession();
        $lastCustomerId = $session->getLastCustomerId();
        if (isset($lastCustomerId) && $session->isLoggedIn() && $lastCustomerId != $session->getId()) {
            $session->unsBeforeAuthUrl()
                ->setLastCustomerId($session->getId());
        }
        if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {
            // Set default URL to redirect customer to
            $session->setBeforeAuthUrl(Mage::helper('Magento_Customer_Helper_Data')->getAccountUrl());
            // Redirect customer to the last page visited after logging in
            if ($session->isLoggedIn()) {
                if (!Mage::getStoreConfigFlag(
                    Magento_Customer_Helper_Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD
                )) {
                    $referer = $this->getRequest()->getParam(Magento_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
                    if ($referer) {
                        $referer = Mage::helper('Magento_Core_Helper_Data')->urlDecode($referer);
                        if ($this->_isUrlInternal($referer)) {
                            $session->setBeforeAuthUrl($referer);
                        }
                    }
                } elseif ($session->getAfterAuthUrl()) {
                    $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
                }
            } else {
                $session->setBeforeAuthUrl(Mage::helper('Magento_Customer_Helper_Data')->getLoginUrl());
            }
        } elseif ($session->getBeforeAuthUrl() == Mage::helper('Magento_Customer_Helper_Data')->getLogoutUrl()) {
            $session->setBeforeAuthUrl(Mage::helper('Magento_Customer_Helper_Data')->getDashboardUrl());
        } else {
            if (!$session->getAfterAuthUrl()) {
                $session->setAfterAuthUrl($session->getBeforeAuthUrl());
            }
            if ($session->isLoggedIn()) {
                $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
            }
        }
        $this->_redirectUrl($session->getBeforeAuthUrl(true));
    }

    /**
     * Customer logout action
     */
    public function logoutAction()
    {
        $lastCustomerId = $this->_getSession()->getId();
        $this->_getSession()->logout()
            ->renewSession()
            ->setBeforeAuthUrl($this->_getRefererUrl())
            ->setLastCustomerId($lastCustomerId);

        $this->_redirect('*/*/logoutSuccess');
    }

    /**
     * Logout success page
     */
    public function logoutSuccessAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Customer register form page
     */
    public function createAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*');
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('Magento_Customer_Model_Session');
        $this->renderLayout();
    }

    /**
     * Create customer account action
     */
    public function createPostAction()
    {
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session->setEscapeMessages(true); // prevent XSS injection in user input

        if (!$this->getRequest()->isPost()) {
            $this->_redirectError(Mage::getUrl('*/*/create', array('_secure' => true)));
            return;
        }

        try {
            $customer = $this->_extractCustomer();
            $address = $this->_extractAddress($customer);
            $this->_validateCustomer($customer, $address);

            $customer->save()->setOrigData();
            $this->_eventManager->dispatch('customer_register_success',
                array('account_controller' => $this, 'customer' => $customer)
            );

            $newLinkToken = $this->_objectManager->get('Magento_Customer_Helper_Data')
                ->generateResetPasswordLinkToken();
            $customer->changeResetPasswordLinkToken($newLinkToken);

            if ($customer->isConfirmationRequired()) {
                $customer->sendNewAccountEmail(
                    'confirmation',
                    $session->getBeforeAuthUrl(),
                    Mage::app()->getStore()->getId()
                );
                $email = Mage::helper('Magento_Customer_Helper_Data')->getEmailConfirmationUrl($customer->getEmail());
                $session->addSuccess(
                    __('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%1">click here</a>.', $email)
                );
                $this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure' => true)));
            } else {
                $session->setCustomerAsLoggedIn($customer);
                $url = $this->_welcomeCustomer($customer);
                $this->_redirectSuccess($url);
            }
            return;
        } catch (Magento_Core_Exception $e) {
            if ($e->getCode() === Magento_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                $url = Mage::getUrl('customer/account/forgotpassword');
                $message = __('There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.', $url);
                $session->setEscapeMessages(false);
            } else {
                $message = $e->getMessage();
            }
            $session->addError($message);
        } catch (Magento_Validator_Exception $e) {
            foreach ($e->getMessages() as $messages) {
                foreach ($messages as $message) {
                    $session->addError($message);
                }
            }
        } catch (Exception $e) {
            $session->addException($e, __('Cannot save the customer.'));
        }

        $session->setCustomerFormData($this->getRequest()->getPost());
        $this->_redirectError(Mage::getUrl('*/*/create', array('_secure' => true)));
    }

    /**
     * Do validation of customer and its address using validate methods in models
     *
     * @param Magento_Customer_Model_Customer $customer
     * @param Magento_Customer_Model_Address|null $address
     * @throws Magento_Validator_Exception
     */
    protected function _validateCustomer($customer, $address = null)
    {
        $errors = array();
        if ($address) {
            $addressErrors = $address->validate();
            if (is_array($addressErrors)) {
                $errors = array_merge($errors, $addressErrors);
            }
        }
        $customerErrors = $customer->validate();
        if (is_array($customerErrors)) {
            $errors = array_merge($errors, $customerErrors);
        }
        if (count($errors) > 0) {
            throw new Magento_Validator_Exception(array($errors));
        }
        // Empty password confirmation data (it is needed only for validation purposes and is not meant to be stored)
        $customer->setConfirmation(null);
    }

    /**
     * Add address to customer during create account
     *
     * @param Magento_Customer_Model_Customer $customer
     * @return Magento_Customer_Model_Address|null
     */
    protected function _extractAddress($customer)
    {
        if (!$this->getRequest()->getPost('create_address')) {
            return null;
        }
        /* @var Magento_Customer_Model_Address $address */
        $address = Mage::getModel('Magento_Customer_Model_Address');
        /* @var Magento_Customer_Model_Form $addressForm */
        $addressForm = Mage::getModel('Magento_Customer_Model_Form');
        $addressForm->setFormCode('customer_register_address')
            ->setEntity($address);

        $addressData = $addressForm->extractData($this->getRequest(), 'address', false);
        $address->setId(null)
            ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
            ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
        $addressForm->compactData($addressData);
        $customer->addAddress($address);
        return $address;
    }

    /**
     * Extract customer entity from request
     *
     * @return Magento_Customer_Model_Customer
     */
    protected function _extractCustomer()
    {
        /** @var Magento_Customer_Model_Customer $customer */
        $customer = Mage::registry('current_customer');
        if (!$customer) {
            $customer = Mage::getModel('Magento_Customer_Model_Customer')->setId(null);
        }
        /* @var Magento_Customer_Model_Form $customerForm */
        $customerForm = Mage::getModel('Magento_Customer_Model_Form');
        $customerForm->setFormCode('customer_account_create')
            ->setEntity($customer);

        $customerData = $customerForm->extractData($this->getRequest());
        // Initialize customer group id
        $customer->getGroupId();
        $customerForm->compactData($customerData);
        $customer->setPassword($this->getRequest()->getPost('password'));
        $customer->setConfirmation($this->getRequest()->getPost('confirmation'));
        if ($this->getRequest()->getParam('is_subscribed', false)) {
            $customer->setIsSubscribed(1);
        }
        return $customer;
    }

    /**
     * Add welcome message and send new account email.
     * Returns success URL
     *
     * @param Magento_Customer_Model_Customer $customer
     * @param bool $isJustConfirmed
     * @return string
     */
    protected function _welcomeCustomer(Magento_Customer_Model_Customer $customer, $isJustConfirmed = false)
    {
        $this->_getSession()->addSuccess(
            __('Thank you for registering with %1.', Mage::app()->getStore()->getFrontendName())
        );
        if ($this->_isVatValidationEnabled()) {
            // Show corresponding VAT message to customer
            $configAddressType = Mage::helper('Magento_Customer_Helper_Address')->getTaxCalculationAddressType();
            $editAddersUrl = Mage::getUrl('customer/address/edit');
            switch ($configAddressType) {
                case Magento_Customer_Model_Address_Abstract::TYPE_SHIPPING:
                    $userPrompt = __('If you are a registered VAT customer, please click <a href="%1">here</a> to enter you shipping address for proper VAT calculation', $editAddersUrl);
                    break;
                default:
                    $userPrompt = __('If you are a registered VAT customer, please click <a href="%1">here</a> to enter you billing address for proper VAT calculation', $editAddersUrl);
                    break;
            }
            $this->_getSession()->addSuccess($userPrompt);
        }

        $customer->sendNewAccountEmail(
            $isJustConfirmed ? 'confirmed' : 'registered',
            '',
            Mage::app()->getStore()->getId()
        );

        $successUrl = Mage::getUrl('*/*/index', array('_secure' => true));
        if (!Mage::getStoreConfigFlag(Magento_Customer_Helper_Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD)
            && $this->_getSession()->getBeforeAuthUrl()
        ) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }
        return $successUrl;
    }

    /**
     * Confirm customer account by id and confirmation key
     */
    public function confirmAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        try {
            $customerId = $this->getRequest()->getParam('id', false);
            $key     = $this->getRequest()->getParam('key', false);
            $backUrl = $this->getRequest()->getParam('back_url', false);
            if (empty($customerId) || empty($key)) {
                throw new Exception(__('Bad request.'));
            }

            // load customer by id (try/catch in case if it throws exceptions)
            try {
                /** @var Magento_Customer_Model_Customer $customer */
                $customer = Mage::getModel('Magento_Customer_Model_Customer')->load($customerId);
                if ((!$customer) || (!$customer->getId())) {
                    throw new Exception('Failed to load customer by id.');
                }
            } catch (Exception $e) {
                throw new Exception(__('Wrong customer account specified.'));
            }

            // check if it is inactive
            if ($customer->getConfirmation()) {
                if ($customer->getConfirmation() !== $key) {
                    throw new Exception(__('Wrong confirmation key.'));
                }

                // activate customer
                try {
                    $customer->setConfirmation(null);
                    $customer->save();
                } catch (Exception $e) {
                    throw new Exception(__('Failed to confirm customer account.'));
                }

                // log in and send greeting email, then die happy
                $this->_getSession()->setCustomerAsLoggedIn($customer);
                $successUrl = $this->_welcomeCustomer($customer, true);
                $this->_redirectSuccess($backUrl ? $backUrl : $successUrl);
                return;
            }

            // die happy
            $this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure' => true)));
            return;
        } catch (Exception $e) {
            // die unhappy
            $this->_getSession()->addError($e->getMessage());
            $this->_redirectError(Mage::getUrl('*/*/index', array('_secure' => true)));
            return;
        }
    }

    /**
     * Send confirmation link to specified email
     */
    public function confirmationAction()
    {
        $customer = Mage::getModel('Magento_Customer_Model_Customer');
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        // try to confirm by email
        $email = $this->getRequest()->getPost('email');
        if ($email) {
            try {
                $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);
                if (!$customer->getId()) {
                    throw new Exception('');
                }
                if ($customer->getConfirmation()) {
                    $customer->sendNewAccountEmail('confirmation', '', Mage::app()->getStore()->getId());
                    $this->_getSession()->addSuccess(__('Please, check your email for confirmation key.'));
                } else {
                    $this->_getSession()->addSuccess(__('This email does not require confirmation.'));
                }
                $this->_getSession()->setUsername($email);
                $this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure' => true)));
            } catch (Exception $e) {
                $this->_getSession()->addException($e, __('Wrong email.'));
                $this->_redirectError(Mage::getUrl('*/*/*', array('email' => $email, '_secure' => true)));
            }
            return;
        }

        // output form
        $this->loadLayout();

        $this->getLayout()->getBlock('accountConfirmation')
            ->setEmail($this->getRequest()->getParam('email', $email));

        $this->_initLayoutMessages('Magento_Customer_Model_Session');
        $this->renderLayout();
    }

    /**
     * Forgot customer password page
     */
    public function forgotPasswordAction()
    {
        $this->loadLayout();

        $this->getLayout()->getBlock('forgotPassword')->setEmailValue(
            $this->_getSession()->getForgottenEmail()
        );
        $this->_getSession()->unsForgottenEmail();

        $this->_initLayoutMessages('Magento_Customer_Model_Session');
        $this->renderLayout();
    }

    /**
     * Forgot customer password action
     */
    public function forgotPasswordPostAction()
    {
        $email = (string)$this->getRequest()->getPost('email');
        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->setForgottenEmail($email);
                $this->_getSession()->addError(__('Please correct the email address.'));
                $this->_redirect('*/*/forgotpassword');
                return;
            }

            /** @var $customer Magento_Customer_Model_Customer */
            $customer = Mage::getModel('Magento_Customer_Model_Customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

            if ($customer->getId()) {
                try {
                    $newPasswordToken = Mage::helper('Magento_Customer_Helper_Data')
                        ->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newPasswordToken);
                    $customer->sendPasswordResetConfirmationEmail();
                } catch (Exception $exception) {
                    $this->_getSession()->addError($exception->getMessage());
                    $this->_redirect('*/*/forgotpassword');
                    return;
                }
            }
            $email = Mage::helper('Magento_Customer_Helper_Data')->escapeHtml($email);
            $this->_getSession()->addSuccess(
                __('If there is an account associated with %1 you will receive an email with a link to reset your password.', $email)
            );
            $this->_redirect('*/*/');
            return;
        } else {
            $this->_getSession()->addError(__('Please enter your email.'));
            $this->_redirect('*/*/forgotpassword');
            return;
        }
    }

    /**
     * Display reset forgotten password form
     *
     * User is redirected on this action when he clicks on the corresponding link in password reset confirmation email
     *
     */
    public function resetPasswordAction()
    {
        $this->_forward('createPassword');
    }

    /**
     * Resetting password handler
     */
    public function createPasswordAction()
    {
        $resetPasswordToken = (string)$this->getRequest()->getParam('token');
        $customerId = (int)$this->getRequest()->getParam('id');
        try {
            $this->_validateResetPasswordLinkToken($customerId, $resetPasswordToken);
            $this->loadLayout();
            // Pass received parameters to the reset forgotten password form
            $this->getLayout()->getBlock('resetPassword')
                ->setCustomerId($customerId)
                ->setResetPasswordLinkToken($resetPasswordToken);
            $this->renderLayout();
        } catch (Exception $exception) {
            $this->_getSession()->addError(
                __('Your password reset link has expired.')
            );
            $this->_redirect('*/*/forgotpassword');
        }
    }

    /**
     * Reset forgotten password
     *
     * Used to handle data received from reset forgotten password form
     *
     */
    public function resetPasswordPostAction()
    {
        $resetPasswordToken = (string)$this->getRequest()->getQuery('token');
        $customerId = (int)$this->getRequest()->getQuery('id');
        $password = (string)$this->getRequest()->getPost('password');
        $passwordConfirmation = (string)$this->getRequest()->getPost('confirmation');

        try {
            $this->_validateResetPasswordLinkToken($customerId, $resetPasswordToken);
        } catch (Exception $exception) {
            $this->_getSession()->addError(
                __('Your password reset link has expired.')
            );
            $this->_redirect('*/*/');
            return;
        }

        $errorMessages = array();
        if (iconv_strlen($password) <= 0) {
            array_push(
                $errorMessages,
                __('New password field cannot be empty.')
            );
        }
        /** @var $customer Magento_Customer_Model_Customer */
        $customer = Mage::getModel('Magento_Customer_Model_Customer')->load($customerId);

        $customer->setPassword($password);
        $customer->setConfirmation($passwordConfirmation);
        $validationErrors = $customer->validate();
        if (is_array($validationErrors)) {
            $errorMessages = array_merge($errorMessages, $validationErrors);
        }

        if (!empty($errorMessages)) {
            $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
            foreach ($errorMessages as $errorMessage) {
                $this->_getSession()->addError($errorMessage);
            }
            $this->_redirect('*/*/createpassword', array(
                'id' => $customerId,
                'token' => $resetPasswordToken
            ));
            return;
        }

        try {
            // Empty current reset password token i.e. invalidate it
            $customer->setRpToken(null);
            $customer->setRpTokenCreatedAt(null);
            $customer->setConfirmation(null);
            $customer->save();
            $this->_getSession()->addSuccess(
                __('Your password has been updated.')
            );
            $this->_redirect('*/*/login');
        } catch (Exception $exception) {
            $this->_getSession()->addException($exception, __('Cannot save a new password.'));
            $this->_redirect('*/*/createpassword', array(
                'id' => $customerId,
                'token' => $resetPasswordToken
            ));
            return;
        }
    }

    /**
     * Check if password reset token is valid
     *
     * @param int $customerId
     * @param string $resetPasswordLinkToken
     * @throws Magento_Core_Exception
     */
    protected function _validateResetPasswordLinkToken($customerId, $resetPasswordLinkToken)
    {
        if (!is_int($customerId)
            || !is_string($resetPasswordLinkToken)
            || empty($resetPasswordLinkToken)
            || empty($customerId)
            || $customerId < 0
        ) {
            throw Mage::exception(
                'Magento_Core',
                __('Invalid password reset token.')
            );
        }

        /** @var $customer Magento_Customer_Model_Customer */
        $customer = Mage::getModel('Magento_Customer_Model_Customer')->load($customerId);
        if (!$customer || !$customer->getId()) {
            throw Mage::exception(
                'Magento_Core',
                __('Wrong customer account specified.')
            );
        }

        $customerToken = $customer->getRpToken();
        if (strcmp($customerToken, $resetPasswordLinkToken) !== 0 || $customer->isResetPasswordLinkTokenExpired()) {
            throw Mage::exception(
                'Magento_Core',
                __('Your password reset link has expired.')
            );
        }
    }

    /**
     * Forgot customer account information page
     */
    public function editAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('Magento_Customer_Model_Session');
        $this->_initLayoutMessages('Magento_Catalog_Model_Session');

        $block = $this->getLayout()->getBlock('customer_edit');
        if ($block) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $data = $this->_getSession()->getCustomerFormData(true);
        $customer = $this->_getSession()->getCustomer();
        if (!empty($data)) {
            $customer->addData($data);
        }
        if ($this->getRequest()->getParam('changepass') == 1) {
            $customer->setChangePassword(1);
        }

        $this->getLayout()->getBlock('head')->setTitle(__('Account Information'));
        $this->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);
        $this->renderLayout();
    }

    /**
     * Change customer password action
     */
    public function editPostAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/edit');
            return;
        }

        if ($this->getRequest()->isPost()) {
            /** @var $customer Magento_Customer_Model_Customer */
            $customer = $this->_getSession()->getCustomer();

            /** @var $customerForm Magento_Customer_Model_Form */
            $customerForm = Mage::getModel('Magento_Customer_Model_Form');
            $customerForm->setFormCode('customer_account_edit')
                ->setEntity($customer);

            $customerData = $customerForm->extractData($this->getRequest());

            $customerForm->compactData($customerData);
            $errors = array();

            // If password change was requested then add it to common validation scheme
            if ($this->getRequest()->getParam('change_password')) {
                $currPass   = $this->getRequest()->getPost('current_password');
                $newPass    = $this->getRequest()->getPost('password');
                $confPass   = $this->getRequest()->getPost('confirmation');

                $oldPass = $this->_getSession()->getCustomer()->getPasswordHash();
                if (Mage::helper('Magento_Core_Helper_String')->strpos($oldPass, ':')) {
                    list(, $salt) = explode(':', $oldPass);
                } else {
                    $salt = false;
                }

                if ($customer->hashPassword($currPass, $salt) == $oldPass) {
                    if (strlen($newPass)) {
                        /**
                         * Set entered password and its confirmation - they
                         * will be validated later to match each other and be of right length
                         */
                        $customer->setPassword($newPass);
                        $customer->setConfirmation($confPass);
                    } else {
                        $errors[] = __('New password field cannot be empty.');
                    }
                } else {
                    $errors[] = __('Invalid current password');
                }
            }

            // Validate account and compose list of errors if any
            $customerErrors = $customer->validate();
            if (is_array($customerErrors)) {
                $errors = array_merge($errors, $customerErrors);
            }

            if (!empty($errors)) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
                foreach ($errors as $message) {
                    $this->_getSession()->addError($message);
                }
                $this->_redirect('*/*/edit');
                return;
            }

            try {
                $customer->setConfirmation(null);
                $customer->save();

                $customer->sendPasswordResetNotificationEmail('reset_frontend');

                $this->_getSession()->setCustomer($customer)
                    ->addSuccess(__('The account information has been saved.'));

                $this->_redirect('customer/account');
                return;
            } catch (Magento_Core_Exception $e) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
                    ->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
                    ->addException($e, __('Cannot save the customer.'));
            }
        }

        $this->_redirect('*/*/edit');
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array $data
     * @return array
     */
    protected function _filterPostData($data)
    {
        $data = $this->_filterDates($data, array('dob'));
        return $data;
    }

    /**
     * Check whether VAT ID validation is enabled
     *
     * @param Magento_Core_Model_Store|string|int $store
     * @return bool
     */
    protected function _isVatValidationEnabled($store = null)
    {
        return Mage::helper('Magento_Customer_Helper_Address')->isVatValidationEnabled($store);
    }
}
