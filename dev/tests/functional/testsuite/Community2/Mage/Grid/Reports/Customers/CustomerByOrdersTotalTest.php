<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Grid_Reports
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Verify of Reports Customer Customer by Orders Total grid
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Community2_Mage_Grid_Report_Customers_CustomerByOrdersTotalTest extends Mage_Selenium_TestCase
{
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
    }

    /**
     * @return array
     *
     */
    protected  function _getTopCustomerNameAndTotalAmount()
    {
        $this->navigate('report_customer_totals');
        $this->gridHelper()->fillDateFromTo();
        $this->clickButton('refresh');
        $gridXpath = $this->_getControlXpath('pageelement', 'customer_by_orders_total_table');
        //get TOP  "Total Order Amount" from first row in grid
        $topOrderAmountXpath = $gridXpath. '/tbody/tr[1]/td[5]';
        if($this->isElementPresent($topOrderAmountXpath))
        {
            $topOrderAmountData = preg_replace("/[^\d.]/", "", $this->getElementByXpath($topOrderAmountXpath));
            //get TOP "Customer Name" from first row in grid
            $topCustomerNameXpath = $gridXpath . '/tbody/tr[1]/td[2]';
            $topCustomerNameData = $this->getElementByXpath($topCustomerNameXpath);
            //get "Number of Orders" from first row in grid
            $topNumberOfOrderXpath = $gridXpath . '/tbody/tr[1]/td[3]';
            $topNumberOfOrderData = $this->getElementByXpath($topNumberOfOrderXpath);

            return array('customer_name'    => $topCustomerNameData,
                         'order_amount'     => $topOrderAmountData,
                         'number_of_orders' => $topNumberOfOrderData
            );
        }

        return null;
    }

    /**
     * <p>Preconditions: create customer and order</p>
     *
     * @test
     */
    public function createEntityInReportGridTest()
    {
        $topReportData = $this->_getTopCustomerNameAndTotalAmount();
        $priceForTestProduct = array();
        if(isset($topReportData))
        {
            $priceForTestProduct['prices_price'] = $topReportData['order_amount'] * 2;
        }
        $simple = $this->loadDataSet('Product', 'simple_product_visible', $priceForTestProduct);
        $userData = $this->loadDataSet('Customers', 'generic_customer_account',
                                 array('first_name' => $this->generate('string', 10, ':alnum:')));
        $addressData = $this->loadDataSet('SalesOrderActions', 'customer_addresses');
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($simple);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData, $addressData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
       $orderData = array('sku'   => $simple['general_name'], 'email' => $userData['email']);
        $orderCreationData = $this->loadDataSet('SalesOrderActions', 'order_data',
            array('filter_sku' => $orderData['sku'], 'email'      => $orderData['email']));
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderCreationData);
        $this->assertMessagePresent('success', 'success_created_order');

        return array('first_name'=>$userData['first_name'],
                     'last_name'=>$userData['last_name'],
                     'email' => $userData['email'],
                     'price' => $simple['prices_price'],
                     'sku'   => $simple['general_name']
        );
    }

    /**
     * <p>Verifying that number of Orders and total amount are increased after create new order</p>
     * <p>Preconditions: </p>
     * <p>1. Test Customer is created</p>
     * <p>2. Test product with price </p>
     * <p>2.1 Login to backend</p>
     * <p>2.2 Go to Reports>Customers>Customer by orders total and check Top Order Amout value</p>
     * <p>2.3 Create simple product with price = (Top Order Amout value) * 2</p>
     * <p>Steps to reproduce:</p>
     *
     * <p>1. Log in to backend as admin</p>
     * <p>2. Create the first order with test product</p>
     * <p>3. Go to Report>Customers> Orders total</p>
     * <p>4. Create the second order with test product</p>
     * <p>5. Go to Report>Customers> Orders total</p>
     * <p>Expected results:</p>
     * <p>After step 3: TOP line in grid conatains First and Last name of test customer, number of order =1. Total amount is equals of simple test product price</p>
     * <p>After step 5: TOP line in grid conatains First and Last name of test customer, number of order =2. Total amount is equals of simple test product price *2</p>
     *
     * @depends createEntityInReportGridTest
     *
     * @test
     * @TestlinkId TL-MAGE-6442
     */
    public function verifyDataInGridTest($testOrderData)
    {
        $topReportGridData = $this->_getTopCustomerNameAndTotalAmount();

        $this->assertEquals($topReportGridData['customer_name'], $testOrderData['first_name'] . ' '
                             . $testOrderData['last_name'], 'Customer Name is wrong' );
        $this->assertEquals(1 , $topReportGridData['number_of_orders'], 'Number of orders is wrong');
        $this->assertEquals($testOrderData['price'] , $topReportGridData['order_amount'], 'Order amount is wrong');
        $this->navigate('manage_sales_orders');
        $orderCreationData = $this->loadDataSet('SalesOrderActions', 'order_data',
            array('filter_sku' => $testOrderData['sku'], 'email' => $testOrderData['email']));
        $this->orderHelper()->createOrder($orderCreationData);
        $this->assertMessagePresent('success', 'success_created_order');
        $topReportGridDataUpdated = $this->_getTopCustomerNameAndTotalAmount();
        $this->assertEquals($topReportGridDataUpdated['customer_name'], $testOrderData['first_name'] . ' '
                             . $testOrderData['last_name'], 'Customer Name is wrong' );
        $this->assertEquals(2 , $topReportGridDataUpdated['number_of_orders'], 'Number of orders is wrong');
        $this->assertEquals($testOrderData['price'] * 2 , $topReportGridDataUpdated['order_amount'],
            'Order amount is wrong');
    }
}