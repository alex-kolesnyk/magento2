<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_ValidationVatNumber
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_ValidationVatNumber_AutomaticAssignmentGroupsFrontendTest extends Mage_Selenium_TestCase
{
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('VatID/store_information_data');
        $this->systemConfigurationHelper()->expandFieldSet('store_information');
        $this->clickControl('button', 'validate_vat_number', false);
        $this->pleaseWait();
        //Verification
        $this->assertTrue($this->controlIsPresent('button', 'vat_number_is_valid'), 'VAT Number is not valid');
    }

    protected function tearDownAfterTest()
    {
        $this->frontend();
        $this->logoutCustomer();
    }

    protected function tearDownAfterTestClass()
    {
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('VatID/create_new_account_options_disable');
    }

    /**
     * @test
     * @return array
     */
    public function preconditionsForTests()
    {
        //Data
        $names = array('group_valid_vat_domestic'   => 'Valid VAT Domestic_%randomize%',
                       'group_valid_vat_intraunion' => 'Valid VAT IntraUnion_%randomize%',
                       'group_invalid_vat'          => 'Invalid VAT_%randomize%',
                       'group_default'              => 'Default Group_%randomize%');
        $processedGroupNames = array();
        //Creating three Customer  Groups
        $this->loginAdminUser();
        $this->navigate('manage_customer_groups');
        foreach ($names as $groupKey => $groupName) {
            $group = $this->loadDataSet('CustomerGroup', 'new_customer_group', array('group_name' => $groupName));
            $this->customerGroupsHelper()->createCustomerGroup($group);
            //Verifying
            $this->assertMessagePresent('success', 'success_saved_customer_group');
            $processedGroupNames[$groupKey] = $group['group_name'];
        }
        //Configuring "Create New Account Options" tab
        $this->navigate('system_configuration');
        $accountOptions = $this->loadDataSet('VatID', 'create_new_account_options', $processedGroupNames);
        $this->systemConfigurationHelper()->configure($accountOptions);

        return $processedGroupNames;
    }

    /**
     * <p>Customer registration. With valid VAT Number for domestic country</p>
     * <p>Steps:</p>
     * <p>1. Goto on front-end</p>
     * <p>2. Create new customer</p>
     * <p>3. Goto tab Address and enter the same country as country of your store</p>
     * <p>4. Enter valid VAT Number</p>
     * <p>4. Goto back-end and open "Manage Customers"</p>
     * <p>5. Select your customer and verify value of Group</p>
     * <p>Expected result:</p>
     * <p>Customer should be assigned to group which was specified as "Group for Valid VAT ID - Domestic"</p>
     *
     * @param array $vatGroup
     * @param array|string $vatNumber
     * @param array|string $customerGroup
     *
     * @test
     * @depends preconditionsForTests
     * @dataProvider dataForCustomersDataProvider
     *
     * @TestlinkId TL-MAGE-6203, TL-MAGE-6204,  TL-MAGE-6205
     */
    public function AutomaticAssignmentGroupsBackendTest($vatNumber, $customerGroup, $vatGroup)
    {
        //Data
        $userData = $this->loadDataSet('Customers', 'customer_account_register');
        $addressData = $this->loadDataSet('Customers', 'generic_address', $vatNumber);
        //Steps
        //Creating customer on front-end
        $this->frontend();
        $this->navigate('customer_login');
        $this->customerHelper()->registerCustomer($userData);
        $this->assertMessagePresent('success', 'vat_number_message');
        //Filling Address Book and VAT Number
        $this->navigate('adding_new_address_book');
        $this->fillFieldset($addressData, 'address_book');
        $this->clickButton('save_address');
        //Verifying Customer Group on back-end
        $this->navigate('manage_customers');
        $this->customerHelper()->openCustomer(array('email' => $userData['email']));
        $verificationData = $vatGroup[$customerGroup];
        $this->verifyForm(array('group' => $verificationData, 'account_information'));
    }

    public function dataForCustomersDataProvider()
    {
        return array(
            array(array('country'            => 'Germany', 'state'=> 'Berlin',
                        'billing_vat_number' => '%noValue%', 'default_billing_address' => '%noValue%'),
                  'group_default'),
            array(array('country' => 'Germany', 'state' => 'Berlin', 'billing_vat_number' => '111607872',
                        'default_billing_address' => '%noValue%'),
                  'group_valid_vat_domestic'),
            array(array('country' => 'Germany', 'state' => 'Berlin', 'billing_vat_number' => '11111111',
                        'default_billing_address' => '%noValue%'),
                  'group_invalid_vat'),
            array(array('country' => 'United Kingdom', 'billing_vat_number' => '584451913',
                        'default_billing_address' => '%noValue%'),
                  'group_valid_vat_intraunion')
        );
    }

    /**
     * <p>Customer registration. With invalid VAT Number for domestic country</p>
     * <p>Steps:</p>
     * <p>1. Goto on front-end</p>
     * <p>2. Create new customer</p>
     * <p>3. Goto tab Address and select some country from Europe Union (but not the same as store country)</p>
     * <p>4. Enter valid VAT Number</p>
     * <p>4. Goto back-end and open "Manage Customers"</p>
     * <p>5. Select your customer and verify value of Group</p>
     * <p>Expected result:</p>
     * <p>Customer should be assigned to group which was specified as "Group for Valid VAT ID - Intra-Union"</p>
     *
     * @param array $processedGroupNames
     *
     * @test
     * @depends preconditionsForTests
     * @return array
     *
     * @TestlinkId TL-MAGE-4041
     */
    public function customerWithValidVatIntraUnion($processedGroupNames)
    {
        //Data
        $userRegisterData = $this->loadDataSet('Customers', 'customer_account_register');
        $userAddressData = $this->loadDataSet('Customers', 'generic_address',
            array('country'    => 'United Kingdom',
                  'billing_vat_number' => '584451913',
                  'default_billing_address' => '%noValue%'));
        //Creating customer on front-end
        $this->goToArea('frontend');
        $this->navigate('customer_login');
        $this->customerHelper()->registerCustomer($userRegisterData);
        $this->assertMessagePresent('success', 'vat_number_message');
        //Filling Address Book and VAT Number
        $this->navigate('adding_new_address_book');
        $this->addParameter('VatNumber', $userAddressData['billing_vat_number']);
        $this->fillFieldset($userAddressData, 'address_book');
        $this->clickButton('save_address');
        $this->assertMessagePresent('success', 'success_validate_intraunion_vat');
        //Verifying Customer Group on back-end
        $this->navigate('manage_customers');
        $this->customerHelper()->openCustomer($userRegisterData);
        $this->openTab('account_information');
        $this->verifyForm(array('group' => $processedGroupNames['group_valid_vat_intraunion']),'account_information');
    }

    /**
     * <p>Validation VAT with invalid number</p>
     * <p>Steps:</p>
     * <p>1. Goto on back-end</p>
     * <p>2. Open Store Information tab</p>
     * <p>3. Enter invalid VAT number</p>
     * <p>4. Click button "Validate VAT Number"</p>
     * <p>Expected result:</p>
     * <p>Button "Validate VAT Number" should be changed on red button with text "VAT Number is Invalid"</p>
     *
     * @test
     * @depends preconditionsForTests
     *
     * @TestlinkId TL-MAGE-3801
     */
    public function validationVatNumber()
    {
        //Data
        $storeInfo = $this->loadDataSet('VatID', 'store_information_data',
            array('billing_vat_number' => 'invalid_number'));
        //Filling "Store Information" data and Validation VAT Number
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($storeInfo);
        $this->systemConfigurationHelper()->expandFieldSet('store_information');
        $this->clickControl('button', 'validate_vat_number', false);
        $this->pleaseWait();
        $this->assertTrue($this->controlIsPresent('button', 'vat_number_is_invalid'));
    }
}
