<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Checkout
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

require 'quote_with_address.php';
/** @var Magento_Sales_Model_Quote $quote */

$quote->getShippingAddress()->setShippingMethod('flatrate_flatrate');
$quote->getPayment()->setMethod('ccsave');

$quote->collectTotals();
$quote->save();

$quoteService = new Magento_Sales_Model_Service_Quote($quote);
$quoteService->getQuote()->getPayment()->setMethod('ccsave');
