<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for Magento_Catalog_Model_Product_Attribute_Backend_Sku.
 * @magentoAppArea adminhtml
 */
class Magento_Catalog_Model_Product_Attribute_Backend_SkuTest extends PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGenerateUniqueSkuExistingProduct()
    {
        /** @var $product Magento_Catalog_Model_Product */
        $product = Magento_TestFramework_Helper_Bootstrap::getObjectManager()
            ->create('Magento_Catalog_Model_Product');
        $product->load(1);
        $product->setId(null);
        $this->assertEquals('simple', $product->getSku());
        $product->getResource()->getAttribute('sku')->getBackend()->beforeSave($product);
        $this->assertEquals('simple-1', $product->getSku());
    }

    /**
     * @param $product Magento_Catalog_Model_Product
     * @dataProvider uniqueSkuDataProvider
     */
    public function testGenerateUniqueSkuNotExistingProduct($product)
    {
        $this->assertEquals('simple', $product->getSku());
        $product->getResource()->getAttribute('sku')->getBackend()->beforeSave($product);
        $this->assertEquals('simple', $product->getSku());
    }

    /**
     * @param $product Magento_Catalog_Model_Product
     * @dataProvider uniqueLongSkuDataProvider
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     */
    public function testGenerateUniqueLongSku($product)
    {
        $existedProduct = clone $product;
        $existedProduct->setId(2);
        $existedProduct->save();
        $this->assertEquals('0123456789012345678901234567890123456789012345678901234567890123', $product->getSku());
        $product->getResource()->getAttribute('sku')->getBackend()->beforeSave($product);
        $this->assertEquals('01234567890123456789012345678901234567890123456789012345678901-1', $product->getSku());
    }

    /**
     * Returns simple product
     *
     * @return array
     */
    public function uniqueSkuDataProvider()
    {
        $product = $this->_getProduct();
        return array(array($product));
    }

    /**
     * Returns simple product
     *
     * @return array
     */
    public function uniqueLongSkuDataProvider()
    {
        $product = $this->_getProduct();
        $product->setSku('0123456789012345678901234567890123456789012345678901234567890123'); //strlen === 64
        return array(array($product));
    }

    /**
     * Get product form data provider
     *
     * @return Magento_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        /** @var $product Magento_Catalog_Model_Product */
        $product = Magento_TestFramework_Helper_Bootstrap::getObjectManager()
            ->create('Magento_Catalog_Model_Product');
        $product->setTypeId(Magento_Catalog_Model_Product_Type::TYPE_SIMPLE)
            ->setId(1)
            ->setAttributeSetId(4)
            ->setWebsiteIds(array(1))
            ->setName('Simple Product')
            ->setSku('simple')
            ->setPrice(10)
            ->setDescription('Description with <b>html tag</b>')
            ->setVisibility(Magento_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->setStatus(Magento_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->setCategoryIds(array(2))
            ->setStockData(
                array(
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_qty_decimal' => 0,
                    'is_in_stock' => 1,
                )
            );
        return $product;
    }
}
