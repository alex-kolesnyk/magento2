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

namespace Magento\Checkout\Test\TestCase\Guest\PaypalExpress;

use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;
use Magento\Checkout\Test\Fixture\Checkout;

/**
 * Class ProductPageTest
 * Place order via Express Checkout from product page
 *
 * @package Magento\Checkout\Test\TestCase\Guest\PaypalExpress
 */
class ProductPageTest extends Functional
{
    /**
     * Guest checkout using "Checkout with PayPal" button from product page and Free Shipping
     *
     * @ZephyrId MAGETWO-12415
     */
    public function testCheckoutFreeShipping()
    {
        $this->markTestSkipped('MAGETWO-16653');
        $fixture = Factory::getFixtureFactory()->getMagentoCheckoutPaypalExpress();
        $fixture->persist();

        //Open product page
        $products = $fixture->getProducts();
        $productPage = Factory::getPageFactory()->getCatalogProductView();
        $productPage->init(end($products));
        $productPage->open();

        //Proceed Checkout
        $productPage->getViewBlock()->paypalCheckout();
        $paypalPage = Factory::getPageFactory()->getPaypal();
        $paypalPage->getLoginBlock()->login($fixture->getPaypalCustomer());
        $paypalPage->getReviewBlock()->continueCheckout();
        $checkoutReviewPage = Factory::getPageFactory()->getPaypalExpressReview();
        $checkoutReviewPage->getReviewBlock()->verifyOrderInformation($fixture);
        $checkoutReviewPage->getReviewBlock()->fillTelephone($fixture->getTelephoneNumber());
        $checkoutReviewPage->getReviewBlock()->selectShippingMethod($fixture->getShippingMethods());

        $checkoutReviewPage->getReviewBlock()->placeOrder();

        //Verification
        $orderId = Factory::getPageFactory()->getCheckoutOnepageSuccess()->getSuccessBlock()->getGuestOrderId();
        $this->_verifyOrder($orderId, $fixture);
    }

    /**
     * Verify order in Backend
     *
     * @param string $orderId
     * @param Checkout $fixture
     */
    protected function _verifyOrder($orderId, Checkout $fixture)
    {
        Factory::getApp()->magentoBackendLoginUser();
        $orderPage = Factory::getPageFactory()->getSalesOrder();
        $orderPage->open();
        $orderPage->getOrderGridBlock()->searchAndOpen(array('id' => $orderId));

        $this->assertContains(
            $fixture->getGrandTotal(),
            Factory::getPageFactory()->getSalesOrderView()->getOrderTotalsBlock()->getGrandTotal(),
            'Incorrect grand total value for the order #' . $orderId
        );

        $this->assertContains(
            'Authorized amount of ' . $fixture->getGrandTotal(),
            Factory::getPageFactory()->getSalesOrderView()->getOrderHistoryBlock()->getCommentsHistory(),
            'Incorrect authorized amount value for the order #' . $orderId
        );
    }
}
