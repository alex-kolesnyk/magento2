<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Paypal
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Paypal_Helper extends Mage_Selenium_AbstractHelper
{
    public static $monthMap = array('1'  => '01 - January', '2'  => '02 - February', '3'  => '03 - March',
                                    '4'  => '04 - April', '5'  => '05 - May', '6'  => '06 - June', '7'  => '07 - July',
                                    '8'  => '08 - August', '9'  => '09 - September', '10' => '10 - October',
                                    '11' => '11 - November', '12' => '12 - December');

    /**
     * Verify errors after order submitting. Skip tests if error from Paypal
     */
    public function verifyMagentoPayPalErrors()
    {
        $paypalErrors = array('PayPal gateway rejected the request', 'PayPal gateway has rejected request',
                              'Unable to communicate with the PayPal gateway.',
                              'Please verify the card with the issuer bank before placing the order.',
                              'There was an error processing your order. Please contact us or try again later.');
        $submitErrors = $this->getMessagesOnPage('error,validation,verification');
        foreach ($submitErrors as $error) {
            foreach ($paypalErrors as $paypalError) {
                if (strpos($error, $paypalError) !== false) {
                    $this->skipTestWithScreenshot(self::messagesToString($this->getMessagesOnPage()));
                }
            }
        }
    }

    ################################################################################
    #                                                                              #
    #                                   PayPal Developer                           #
    #                                                                              #
    ################################################################################
    /**
     * Validate paypal Page
     *
     * @param string $page
     */
    public function validatePage($page = '')
    {
        if ($page) {
            $this->assertTrue($this->checkCurrentPage($page), $this->getMessagesOnPage());
        } else {
            $page = $this->_findCurrentPageFromUrl();
        }
        //$expectedTitle = $this->getUimapPage($this->getConfigHelper()->getArea(),
        //$page)->getTitle($this->_paramsHelper);
        //$this->assertSame($expectedTitle, $this->url(), 'Title is unexpected for "' . $page . '" page');
        $this->setCurrentPage($page);
    }

    public function waitForNewPage()
    {
        try {
            parent::waitForNewPage();
        } catch (Exception $e) {
            $this->skipTestWithScreenshot($e->getMessage());
        }
    }

    /**
     * Log into Paypal developer's site
     */
    public function paypalDeveloperLogin()
    {
        try {
            $this->goToArea('paypal_developer', 'paypal_developer_home', false);
        } catch (Exception $e) {
            $this->skipTestWithScreenshot($e->getMessage());
        }
        $loginData = array('login_email'     => $this->getConfigHelper()->getDefaultLogin(),
                           'login_password'  => $this->getConfigHelper()->getDefaultPassword());
        $this->validatePage();
        if ($this->controlIsPresent('button', 'login_with_paypal')) {
            $this->clickButton('login_with_paypal', false);
            $this->selectLastWindow();
            $this->fillForm($loginData);
            $this->getControlElement('button', 'login')->click();
            $this->window('');
            $this->validatePage();
            $this->waitForElement($this->_getControlXpath('button', 'logout'));
        }
        $result = $this->errorMessage();
        $this->assertFalse($result['success'], $this->getMessagesOnPage());
    }

    /**
     * Creates preconfigured Paypal Sandbox account
     *
     * @param string|array $parameters
     *
     * @return array
     */
    public function createPreconfiguredAccount($parameters)
    {
        if (is_string($parameters)) {
            $parameters = $this->loadDataSet('Paypal', $parameters);
        }
        $this->navigate('paypal_developer_create_account');
        $this->fillForm($parameters);
        $this->clickButton('create_account');
        //If get error message after account creation
        $error = $this->errorMessage('failed_account_creation');
        $error1 = $this->successMessage('success_created_account_without_card');
        if ($error['success'] || $error1['success']) {
            $delete = $this->getPaypalSandboxAccountInfo($parameters);
            $this->deleteAccount($delete['email']);
            return $this->createPreconfiguredAccount($parameters);
        }
        $error = $this->errorMessage('incorrect_information');
        if ($error['success']) {
            return $this->createPreconfiguredAccount($parameters);
        }
        $this->assertMessagePresent('success', 'success_created_account');
        $this->validatePage();

        return $this->getPaypalSandboxAccountInfo($parameters);
    }

    /**
     * Gets the email for newly created sandbox account
     *
     * @param array $parameters
     *
     * @return array
     */
    public function getPaypalSandboxAccountInfo(array $parameters)
    {
        $this->openAccountDetailsTab($parameters['login_email'], 'funding_tab');
        $data['email'] = $parameters['login_email'];
        //Get Credit card data
        $data['credit_card']['card_type'] = $parameters['add_credit_card'];
        $this->addParameter('propertyName', 'Credit card number:');
        $data['credit_card']['card_number'] =
            $this->getControlAttribute(self::FIELD_TYPE_PAGEELEMENT, 'property', 'text');

        $this->addParameter('propertyName', 'Expiration date:');
        list($expMonth, $expYear) =
            explode('/', $this->getControlAttribute(self::FIELD_TYPE_PAGEELEMENT, 'property', 'text'));
        $data['credit_card']['expiration_month'] = self::$monthMap[trim($expMonth)];
        $data['credit_card']['expiration_year'] = $expYear;
        $data['credit_card'] = array_map('trim', $data['credit_card']);

        return $data;
    }

    /**
     * Gets API Credentials for account
     *
     * @param string $email
     *
     * @return array
     */
    public function getApiCredentials($email)
    {
        $this->openAccountDetailsTab($email, 'api_credentials_tab');
        $apiCredentials = array();

        $keys = array('api_username','api_password', 'api_signature');
        foreach($keys as $key => $value) {
            $apiCredentials[$value] =
                $this->getControlAttribute(self::FIELD_TYPE_PAGEELEMENT, $value, 'text');
        }
        $apiCredentials['email_associated_with_paypal_merchant_account'] = $email;
        return $apiCredentials;
    }

    /**
     * Open tab on account details popup
     *
     * @param $email Account email address
     * @param $tabName Link to open tab
     */
    private function openAccountDetailsTab($email, $tabName)
    {
        if ('paypal_developer_sandbox_accounts' != $this->getCurrentPage()) {
            $this->navigate('paypal_developer_sandbox_accounts');
        }
        $this->addParameter('accountEmail', $email);
        $this->clickControl(self::FIELD_TYPE_LINK, 'account_details', false);
        $this->waitForControlVisible(self::FIELD_TYPE_LINK, 'account_profile');
        $this->clickControl(self::FIELD_TYPE_LINK, 'account_profile', false);
        $this->waitForControlVisible(self::UIMAP_TYPE_FIELDSET, 'account_details_popup');
        $this->clickControl(self::FIELD_TYPE_LINK, $tabName, false);
    }
    /**
     * Deletes all accounts at PayPal sandbox
     */
    public function deleteAllAccounts()
    {
        //Show 50 accounts per page
        $this->setUrlPostfix('?numAccounts=50');
        $this->navigate('paypal_developer_sandbox_accounts');
        $this->setUrlPostfix(null);
        $this->fillCheckbox('select_all_accounts', 'Yes');
        if (!$this->getControlAttribute('button', 'delete_account', 'disabled')) {
            $this->clickButton('delete_account', false);
            $this->waitForControlVisible(self::UIMAP_TYPE_FIELDSET, 'delete_account_popup');
            $this->clickButton('delete');
        }
    }

    /**
     * Deletes account at PayPal sandbox
     *
     * @param string $email
     */
    public function deleteAccount($email)
    {
        //Show 50 accounts per page
        $this->setUrlPostfix('?numAccounts=50');
        $this->navigate('paypal_developer_sandbox_accounts');
        $this->setUrlPostfix(null);
        $this->addParameter('accountEmail', $email);
        $this->fillCheckbox('select_account', 'Yes');
        if (!$this->getControlAttribute('button', 'delete_account', 'disabled')) {
            $this->clickButton('delete_account', false);
            $this->waitForControlVisible(self::UIMAP_TYPE_FIELDSET, 'delete_account_popup');
            $this->clickButton('delete');
        }
    }

    /**
     * Create Buyers Accounts on PayPal sandbox
     *
     * @param array|string $cards mastercard, visa, discover, amex
     *
     * @return array $accounts
     * @test
     */
    public function createBuyerAccounts($cards)
    {
        if (is_string($cards)) {
            $cards = explode(',', $cards);
            $cards = array_map('trim', $cards);
        }
        $accounts = array();
        foreach ($cards as $card) {
            $info = $this->loadDataSet('Paypal', 'paypal_sandbox_new_buyer_account_' . $card);
            $accounts[$card] = $this->createPreconfiguredAccount($info);
            if ($card != 'amex') {
                $accounts[$card]['credit_card']['card_verification_number'] = '111';
            } else {
                $accounts[$card]['credit_card']['card_verification_number'] = '1234';
            }
        }
        return $accounts;
    }

    ################################################################################
    #                                                                              #
    #                 PayPal Sandbox(@TODO check and rewrite)                      #
    #                                                                              #
    ################################################################################
    /**
     * Login using sandbox account
     * Function has not been verified and is not used right now
     * @TODO check and rewrite
     *
     * @param $parameters
     */
    public function paypalSandboxLogin($parameters)
    {
        $parameters = $this->fixtureDataToArray($parameters);
        if ($this->controlIsPresent('button', 'button_login')) {
            $this->addParameter('elementTitle', $parameters['page_title']);
            $this->validatePage();
            $this->fillForm($parameters['credentials']);
            $this->clickControl('button', 'button_login');
        }
    }

    /**
     * Configure sandbox account
     * Function has not been verified and is not used right now
     * @TODO check and rewrite
     *
     * @param $parameters
     */
    public function paypalSandboxConfigure($parameters)
    {
        $parameters = $this->fixtureDataToArray($parameters);
        $this->addParameter('elementTitle', $parameters['page_title']);
        $this->validatePage();
        $this->fillForm($parameters['credentials']);
        $this->clickControl('button', 'button_login');
        $this->clickControl('button', 'button_agree');
    }

    /**
     * Pays the order using paypal sandbox account
     * Function has not been verified and is not used right now
     * @TODO check and rewrite
     *
     * @param $parameters
     */
    public function paypalPayOrder($parameters)
    {
        $parameters = $this->fixtureDataToArray($parameters);
        if (!$this->controlIsPresent('button', 'button_login')) {
            $this->addParameter('elementTitle', $parameters['page_title_pay_with']);
            $this->validatePage();
            $this->addParameter('elementTitle', $parameters['page_title']);
            $this->clickControl('link', 'have_paypal_account');
        } else {
            $this->addParameter('elementTitle', $parameters['page_title']);
            $this->validatePage();
        }
        $this->fillForm($parameters['credentials']);
        $this->addParameter('elementTitle', $parameters['page_title_review_info']);
        $this->clickControl('button', 'button_login');
        $this->clickControl('button', 'button_continue');
    }
}