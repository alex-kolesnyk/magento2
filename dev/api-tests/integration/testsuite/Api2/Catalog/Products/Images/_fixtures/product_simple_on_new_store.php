<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

require TEST_FIXTURE_DIR . '/Catalog/Category/category_on_new_store.php';

/* @var $productFixture Mage_Catalog_Model_Product */
$product = require TEST_FIXTURE_DIR . '/_block/Catalog/Product.php';
$product->setStoreId(0)
    ->setWebsiteIds(array(Mage::app()->getDefaultStoreView()->getWebsiteId()))
    ->save();
// product should be assigned to website (with appropriate store view) to use store view in rest
$websites = $product->getWebsiteIds();
$websites[] = Magento_Test_Webservice::getFixture('website')->getId();

// to make stock item visible from created product it should be reloaded
$product = Mage::getModel('Mage_Catalog_Model_Product')->load($product->getId());
$product->setStoreId(Magento_Test_Webservice::getFixture('store')->getId())
    ->setWebsiteIds($websites)
    ->save();
Magento_Test_Webservice::setFixture('product_simple', $product);
