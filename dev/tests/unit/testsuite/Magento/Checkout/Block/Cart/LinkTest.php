<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Magento_Checkout_Block_Cart_LinkTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_TestFramework_Helper_ObjectManager
     */
    protected $_objectManagerHelper;

    protected function setUp()
    {
        $this->_objectManagerHelper = new Magento_TestFramework_Helper_ObjectManager($this);
    }

    public function testGetUrl()
    {
        $path = 'checkout/cart';
        $url = 'http://example.com/';

        $urlBuilder = $this->getMockForAbstractClass('Magento_Core_Model_UrlInterface');
        $urlBuilder->expects($this->once())->method('getUrl')->with($path)->will($this->returnValue($url . $path));

        $helper = $this->getMockBuilder('Magento_Core_Helper_Data')
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->_objectManagerHelper->getObject(
            'Magento_Core_Block_Template_Context',
            array('urlBuilder' => $urlBuilder)
        );
        $link = $this->_objectManagerHelper->getObject(
            'Magento_Checkout_Block_Cart_Link',
            array(
                'coreData' => $helper,
                'context' => $context
            )
        );
        $this->assertSame($url . $path, $link->getHref());
    }

    public function testToHtml()
    {
        $moduleManager = $this->getMockBuilder('Magento_Core_Model_ModuleManager')
            ->disableOriginalConstructor()
            ->setMethods(array('isOutputEnabled'))
            ->getMock();
        $helper = $this->getMockBuilder('Magento_Customer_Helper_Data')
            ->disableOriginalConstructor()
            ->getMock();
        $helperFactory = $this->getMockBuilder('Magento_Core_Model_Factory_Helper')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMock();
        $helperFactory->expects($this->any())->method('get')->will($this->returnValue($helper));

        /** @var  Magento_Core_Block_Template_Context $context */
        $context = $this->_objectManagerHelper->getObject(
            'Magento_Core_Block_Template_Context',
            array(
                'helperFactory' => $helperFactory
            )
        );

        /** @var Magento_Invitation_Block_Link $block */
        $block = $this->_objectManagerHelper->getObject(
            'Magento_Checkout_Block_Cart_Link',
            array(
                'context' => $context,
                'moduleManager' => $moduleManager
            )
        );
        $moduleManager->expects($this->any())
            ->method('isOutputEnabled')
            ->with('Magento_Checkout')
            ->will($this->returnValue(true));
        $this->assertSame('', $block->toHtml());
    }

    /**
     * @dataProvider getLabelDataProvider
     */
    public function testGetLabel($productCount, $label)
    {
        $helper = $this->getMockBuilder('Magento_Checkout_Helper_Cart')
            ->disableOriginalConstructor()
            ->setMethods(array('getSummaryCount'))
            ->getMock();
        $helperFactory = $this->getMockBuilder('Magento_Core_Model_Factory_Helper')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMock();
        $helperFactory->expects($this->any())->method('get')->will($this->returnValue($helper));

        /** @var  Magento_Core_Block_Template_Context $context */
        $context = $this->_objectManagerHelper->getObject(
            'Magento_Core_Block_Template_Context',
            array(
                'helperFactory' => $helperFactory
            )
        );

        /** @var Magento_Invitation_Block_Link $block */
        $block = $this->_objectManagerHelper->getObject(
            'Magento_Checkout_Block_Cart_Link',
            array(
                'context' => $context,
            )
        );
        $helper->expects($this->any())->method('getSummaryCount')->will($this->returnValue($productCount));
        $this->assertSame($label, (string)$block->getLabel());
    }

    public function getLabelDataProvider()
    {
        return array(
            array(1, 'My Cart (1 item)'),
            array(2, 'My Cart (2 items)'),
            array(0, 'My Cart')
        );
    }
}