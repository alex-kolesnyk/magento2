<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
include "configurable.php";
define('CONFIGURABLE_ASSIGNED_PRODUCTS_COUNT', 2);
/** @var $configurableProduct Mage_Catalog_Model_Product */
$configurableProduct = Mage::registry('product_configurable');

$productsToAssignIds = array();
for ($i = 0; $i < CONFIGURABLE_ASSIGNED_PRODUCTS_COUNT; $i++) {
    /* @var $product Mage_Catalog_Model_Product */
    $product = require '_fixture/_block/Catalog/Product.php';
    $product->setName("Assigned product #$i")
        ->setAttributeSetId($configurableProduct->getAttributeSetId())
        ->setWebsiteIds($configurableProduct->getWebsiteIds());
    // set configurable attributes values
    for ($attributeCount = 1; $attributeCount <= ATTRIBUTES_COUNT; $attributeCount++) {
        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = Mage::registry("eav_configurable_attribute_$attributeCount");
        $lastOption = end($attribute->getSource()->getAllOptions());
        $product->setData($attribute->getAttributeCode(), $lastOption['value']);
    }
    $product->save();
    $productsToAssignIds[$product->getId()] = $product->getId();
    Mage::register("configurable_assigned_product_$i", $product);
}
$configurableProduct->setConfigurableProductsData($productsToAssignIds)->save();
$configurableProduct->save();
// reload configurable product data after adding of associated products
$configurableProduct = Mage::getModel('Mage_Catalog_Model_Product')->load($configurableProduct->getId());

// set option prices
/** @var $configurableType Mage_Catalog_Model_Product_Type_Configurable */
$configurableType = $configurableProduct->setStoreId(0)->getTypeInstance();
$configurableAttributes = $configurableType->getConfigurableAttributesAsArray($configurableProduct);
foreach ($configurableAttributes as &$configurableAttribute) {
    foreach ($configurableAttribute['values'] as &$value) {
        // generate price from 1.00 to 100.00
        $value['pricing_value'] = rand(100, 10000) / 100;
        $value['is_percent'] = rand(0, 1);
    }
}
$configurableProduct->setConfigurableAttributesData($configurableAttributes)
    ->setCanSaveConfigurableAttributes(true)
    ->save();

// reload configurable product data after adding prices
$configurableProduct = Mage::getModel('Mage_Catalog_Model_Product')->load($configurableProduct->getId());
Mage::register("product_configurable", $configurableProduct);

