<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Customer
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for Mage_Customer_Block_Account_Authorization
 */
class Mage_Customer_Block_Account_AuthorizationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Test_Helper_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Customer_Model_Session
     */
    protected $_session;

    /**
     * @var Mage_Customer_Helper_Data
     */
    protected $_helper;

    /**
     * @var Mage_Customer_Block_Account_Authorization
     */
    protected $_block;

    public function setUp()
    {
        $this->_objectManager = new Magento_Test_Helper_ObjectManager($this);
        $this->_session = $this->getMockBuilder('Mage_Customer_Model_Session')
            ->disableOriginalConstructor()
            ->setMethods(array('isLoggedIn'))
            ->getMock();
        $this->_helper = $this->getMockBuilder('Mage_Customer_Helper_Data')
            ->disableOriginalConstructor()
            ->setMethods(array('getLogoutUrl', 'getLoginUrl'))
            ->getMock();

        $context = $this->_objectManager->getObject('Mage_Core_Block_Template_Context');

        $context->getHelperFactory()->expects($this->any())->method('get')->will($this->returnValue($this->_helper));

        $this->_block = $this->_objectManager->getObject(
            'Mage_Customer_Block_Account_Authorization',
            array(
                'context' => $context,
                'session' => $this->_session,
            )
        );
    }

    public function testGetLabelLoggedIn()
    {
        $this->_session->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));

        $this->assertEquals('Log Out', $this->_block->getLabel());
    }

    public function testGetLabelLoggedOut()
    {
        $this->_session->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->assertEquals('Log In', $this->_block->getLabel());
    }

    public function testGetHrefLoggedIn()
    {
        $this->_session->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));

        $this->_helper->expects($this->once())->method('getLogoutUrl')->will($this->returnValue('logout url'));

        $this->assertEquals('logout url', $this->_block->getHref());
    }

    public function testGetHrefLoggedOut()
    {
        $this->_session->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->_helper->expects($this->once())->method('getLoginUrl')->will($this->returnValue('login url'));

        $this->assertEquals('login url', $this->_block->getHref());
    }
}
