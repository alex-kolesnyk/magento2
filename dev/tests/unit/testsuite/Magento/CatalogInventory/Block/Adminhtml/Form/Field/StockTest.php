<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Magento_CatalogInventory_Block_Adminhtml_Form_Field_StockTest extends PHPUnit_Framework_TestCase
{
    const ATTRIBUTE_NAME = 'quantity_and_stock_status';

    /**
     * @var Magento_CatalogInventory_Block_Adminhtml_Form_Field_Stock
     */
    protected $_model;

    /**
     * @var Magento_Data_Form_Element_Text
     */
    protected $_qty;

    protected function setUp()
    {
        $elementFactory = $this->getMock('Magento_Data_Form_ElementFactory', null, array(), '', false);
        $this->_qty = $this->getMock(
            'Magento_Data_Form_Element_Text',
            array('getElementHtml', 'setForm', 'setValue', 'setName'),
            array($elementFactory, array())
        );
        $this->_model = $this->getMock(
            'Magento_CatalogInventory_Block_Adminhtml_Form_Field_Stock',
            array('getElementHtml'),
            array($elementFactory, array('qty' => $this->_qty, 'name' => self::ATTRIBUTE_NAME))
        );
    }

    public function testGetElementHtml()
    {
        $this->_qty->expects($this->once())->method('getElementHtml')->will($this->returnValue('html'));
        $this->_model->expects($this->once())->method('getElementHtml')
            ->will($this->returnValue($this->_qty->getElementHtml()));
        $this->assertEquals('html', $this->_model->getElementHtml());
    }

    public function testSetForm()
    {
        $textElement = $this->getMock('Magento_Data_Form_Element_Text', null, array(), '', false);
        $this->_qty->expects($this->once())->method('setForm')
            ->with($this->isInstanceOf('Magento_Data_Form_Element_Abstract'));
        $this->_model->setForm($textElement);
    }

    public function testSetValue()
    {
        $value = array('qty' => 1, 'is_in_stock' => 0);
        $this->_qty->expects($this->once())->method('setValue')->with($this->equalTo(1));
        $this->_model->setValue($value);
    }

    public function testSetName()
    {
        $this->_qty->expects($this->once())->method('setName')->with(self::ATTRIBUTE_NAME . '[qty]');
        $this->_model->setName(self::ATTRIBUTE_NAME);
    }
}
