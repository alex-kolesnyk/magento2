<?php
/**
 * {license_notice}
 *
 * @category    Mtf
 * @package     Mtf
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Checkout\Test\Fixture;

use Mtf\Factory\Factory;

/**
 * Class GuestPaypalExpress
 * PayPal Express Method
 * Guest checkout using PayPal Express Checkout method and offline shipping method
 *
 * @ZephyrId MAGETWO-12413
 * @package Magento\Checkout\Test\Fixture
 */
class GuestPaypalExpress extends Checkout
{
    /**
     * Paypal customer buyer
     *
     * @var \Magento\Paypal\Test\Fixture\Customer
     */
    private $paypalCustomer;

    /**
     * Get Paypal buyer account
     *
     * @return \Magento\Paypal\Test\Fixture\Customer
     */
    public function getPaypalCustomer()
    {
        return $this->paypalCustomer;
    }

    /**
     * Prepare data for guest checkout with PayPal Express
     */
    protected function _initData()
    {
        //Configuration
        $this->_persistConfiguration(array(
            'flat_rate',
            'paypal_disabled_all_methods',
            'paypal_express',
            'display_price',
            'display_shopping_cart',
            'default_tax_config'
        ));
        //Tax
        $taxRule = Factory::getFixtureFactory()->getMagentoTaxTaxRule();
        $taxRule->switchData('custom_rule');
        $taxRule->persist();
        //Products
        $simple = Factory::getFixtureFactory()->getMagentoCatalogProduct();
        $simple->switchData('simple');

        $configurable = Factory::getFixtureFactory()->getMagentoCatalogConfigurableProduct();

        $bundle = Factory::getFixtureFactory()->getMagentoBundleBundle();;

        $simple->persist();
        $configurable->persist();
        $bundle->persist();

        $this->products = array(
            $simple,
            $configurable,
            $bundle
        );
        //Checkout data
        $this->billingAddress = Factory::getFixtureFactory()->getMagentoCustomerAddress();
        $this->billingAddress->switchData('address_US_3');

        $this->shippingMethods = Factory::getFixtureFactory()->getMagentoShippingMethod();
        $this->shippingMethods->switchData('flat_rate');

        $this->paymentMethod = Factory::getFixtureFactory()->getMagentoPaymentMethod();
        $this->paymentMethod->switchData('paypal_express');

        $this->creditCard = Factory::getFixtureFactory()->getMagentoPaymentCc();
        $this->creditCard->switchData('visa_direct');

        $this->paypalCustomer = Factory::getFixtureFactory()->getMagentoPaypalCustomer();
        $this->paypalCustomer->switchData('customer_US');

        //Verification data
        $this->_data = array(
            'totals' => array(
                'grand_total' => '$166.72',
                'authorized_amount' => '$166.72',
                'comment_history'   => 'Authorized amount of $166.72',
            )
        );
    }
}
