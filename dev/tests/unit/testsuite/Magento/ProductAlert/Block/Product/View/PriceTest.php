<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_ProductAlert
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\ProductAlert\Block\Product\View;

/**
 * Test class for \Magento\ProductAlert\Block\Product\View\Price
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|\Magento\ProductAlert\Helper\Data
     */
    protected $_helper;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|\Magento\Core\Model\Registry
     */
    protected $_registry;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|\Magento\ProductAlert\Block\Product\View\Price
     */
    protected $_block;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|\Magento\Core\Model\Layout
     */
    protected $_layout;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_helper = $this->getMock(
            'Magento\ProductAlert\Helper\Data', array('isPriceAlertAllowed', 'getSaveUrl'), array(), '', false
        );
        $this->_product = $this->getMock(
            'Magento\Catalog\Model\Product', array('getCanShowPrice', 'getId', '__wakeup'), array(), '', false
        );
        $this->_product->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->_registry = $this->getMockBuilder('Magento\Core\Model\Registry')
            ->disableOriginalConstructor()
            ->setMethods(array('registry'))
            ->getMock();
        $this->_block = $objectManager->getObject(
            'Magento\ProductAlert\Block\Product\View\Price',
            array(
                'helper' => $this->_helper,
                'registry' => $this->_registry,
            )
        );
        $this->_layout = $this->getMock('Magento\Core\Model\Layout', array(), array(), '', false);
    }

    public function testSetTemplatePriceAlertAllowed()
    {
        $this->_helper->expects($this->once())->method('isPriceAlertAllowed')->will($this->returnValue(true));
        $this->_helper
            ->expects($this->once())
            ->method('getSaveUrl')
            ->with('price')
            ->will($this->returnValue('http://url'))
        ;

        $this->_product->expects($this->once())->method('getCanShowPrice')->will($this->returnValue(true));

        $this->_registry->expects($this->once())
            ->method('registry')
            ->with('current_product')
            ->will($this->returnValue($this->_product));

        $this->_block->setLayout($this->_layout);
        $this->_block->setTemplate('path/to/template.phtml');

        $this->assertEquals('path/to/template.phtml', $this->_block->getTemplate());
        $this->assertEquals('http://url', $this->_block->getSignupUrl());
    }

    /**
     * @param bool $priceAllowed
     * @param bool $showProductPrice
     *
     * @dataProvider setTemplatePriceAlertNotAllowedDataProvider
     */
    public function testSetTemplatePriceAlertNotAllowed($priceAllowed, $showProductPrice)
    {
        $this->_helper->expects($this->once())->method('isPriceAlertAllowed')->will($this->returnValue($priceAllowed));
        $this->_helper->expects($this->never())->method('getSaveUrl');

        $this->_product->expects($this->any())->method('getCanShowPrice')->will($this->returnValue($showProductPrice));

        $this->_registry->expects($this->once())
            ->method('registry')
            ->with('current_product')
            ->will($this->returnValue($this->_product));

        $this->_block->setLayout($this->_layout);
        $this->_block->setTemplate('path/to/template.phtml');

        $this->assertEquals('', $this->_block->getTemplate());
        $this->assertNull($this->_block->getSignupUrl());
    }

    /**
     * @return array
     */
    public function setTemplatePriceAlertNotAllowedDataProvider()
    {
        return array(
            'price alert is not allowed' => array(false, true),
            'no product price'  => array(true, false),
            'price alert is not allowed and no product price' => array(false, false),
        );
    }

    public function testSetTemplateNoProduct()
    {
        $this->_helper->expects($this->once())->method('isPriceAlertAllowed')->will($this->returnValue(true));
        $this->_helper->expects($this->never())->method('getSaveUrl');

        $this->_registry->expects($this->once())
            ->method('registry')
            ->with('current_product')
            ->will($this->returnValue(null));

        $this->_block->setLayout($this->_layout);
        $this->_block->setTemplate('path/to/template.phtml');

        $this->assertEquals('', $this->_block->getTemplate());
        $this->assertNull($this->_block->getSignupUrl());
    }
}
