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
 * Test class for Magento_Catalog_Model_Layer_Filter_Decimal.
 *
 * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/_files/attribute_weight_filterable.php
 * @magentoDataFixture Magento/Catalog/_files/categories.php
 */
class Magento_Catalog_Model_Layer_Filter_DecimalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Catalog_Model_Layer_Filter_Decimal
     */
    protected $_model;

    protected function setUp()
    {
        $category = Mage::getModel('Magento_Catalog_Model_Category');
        $category->load(4);

        /** @var $attribute Magento_Catalog_Model_Entity_Attribute */
        $attribute = Mage::getModel('Magento_Catalog_Model_Entity_Attribute');
        $attribute->loadByCode('catalog_product', 'weight');

        $this->_model = Mage::getModel('Magento_Catalog_Model_Layer_Filter_Decimal');
        $this->_model->setData(array(
            'layer' => Mage::getModel('Magento_Catalog_Model_Layer', array(
                'data' => array('current_category' => $category)
            )),
            'attribute_model' => $attribute,
        ));
    }

    public function testApplyNothing()
    {
        $this->assertEmpty($this->_model->getData('range'));

        $this->_model->apply(
            new Magento_TestFramework_Request(),
            Mage::app()->getLayout()->createBlock('Magento_Core_Block_Text')
        );

        $this->assertEmpty($this->_model->getData('range'));
    }

    public function testApplyInvalid()
    {
        $this->assertEmpty($this->_model->getData('range'));

        $request = new Magento_TestFramework_Request();
        $request->setParam('decimal', 'non-decimal');
        $this->_model->apply($request, Mage::app()->getLayout()->createBlock('Magento_Core_Block_Text'));

        $this->assertEmpty($this->_model->getData('range'));
    }

    public function testApply()
    {
        $request = new Magento_TestFramework_Request();
        $request->setParam('decimal', '1,100');
        $this->_model->apply($request, Mage::app()->getLayout()->createBlock('Magento_Core_Block_Text'));

        $this->assertEquals(100, $this->_model->getData('range'));
    }

    public function testGetMaxValue()
    {
        $this->assertEquals(56.00, $this->_model->getMaxValue());
    }

    public function testGetMinValue()
    {
        $this->assertEquals(18.00, $this->_model->getMinValue());
    }

    public function testGetRange()
    {
        $this->assertEquals(10, $this->_model->getRange());
    }

    public function getRangeItemCountsDataProvider()
    {
        return array(
            array(1,  array(19 => 1, 57 => 1)),
            array(10, array(2  => 1, 6  => 1)),
            array(30, array(1  => 1, 2  => 1)),
            array(60, array(1  => 2)),
        );
    }

    /**
     * @dataProvider getRangeItemCountsDataProvider
     */
    public function testGetRangeItemCounts($inputRange, $expectedItemCounts)
    {
        $this->assertEquals($expectedItemCounts, $this->_model->getRangeItemCounts($inputRange));
    }
}
