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

namespace Magento\Catalog\Helper\Product;

class FlatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Product\Flat
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Catalog\Helper\Product\Flat');
    }

    public function testGetFlag()
    {
        $flag = $this->_helper->getFlag();
        $this->assertInstanceOf('Magento\Catalog\Model\Product\Flat\Flag', $flag);
    }

    public function testIsBuilt()
    {
        $this->assertFalse($this->_helper->isBuilt());
        $flag = $this->_helper->getFlag();
        try {
            $flag->setIsBuilt(true);
            $this->assertTrue($this->_helper->isBuilt());

            $flag->setIsBuilt(false);
        } catch (\Exception $e) {
            $flag->setIsBuilt(false);
            throw $e;
        }
    }

    public function testIsEnabledDefault()
    {

        $this->assertFalse($this->_helper->isEnabled());
    }

    /**
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     */
    public function testIsEnabled()
    {
        $this->assertTrue($this->_helper->isEnabled());
    }

    public function testIsAddFilterableAttributesDefault()
    {
        $this->assertEquals(0, $this->_helper->isAddFilterableAttributes());
    }

    /**
     * @magentoConfigFixture global/catalog/product/flat/add_filterable_attributes 1
     */
    public function testIsAddFilterableAttributes()
    {
        $this->assertEquals(1, $this->_helper->isAddFilterableAttributes());
    }

    public function testIsAddChildDataDefault()
    {
        $this->assertEquals(0, $this->_helper->isAddChildData());
    }

    /**
     * @magentoConfigFixture global/catalog/product/flat/add_child_data 1
     */
    public function testIsAddChildData()
    {
        $this->assertEquals(1, $this->_helper->isAddChildData());
    }
}
