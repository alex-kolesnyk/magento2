<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Core\Controller\Varien;

/**
 * Test class \Magento\Core\Controller\Varien\AbstractAction
 */
class AbstractActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Controller\Varien\AbstractAction
     */
    protected $_actionAbstract;

    /**
     * @var \Magento\Core\Controller\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var \Magento\Core\Controller\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_response;

    /**
     * Setup before tests
     *
     * Create request, response and forward action (child of AbstractAction)
     */
    protected function setUp()
    {
        $helperMock = $this->getMock( 'Magento\Backend\Helper\DataProxy', array(), array(), '', false);
        $this->_request = $this->getMock(
            'Magento\Core\Controller\Request\Http',
            array('getRequestedRouteName', 'getRequestedControllerName', 'getRequestedActionName'),
            array($helperMock),
            '',
            false
        );
        $this->_response = $this->getMock('Magento\Core\Controller\Response\Http', array(), array(), '', false);
        $this->_response->headersSentThrowsException = false;
        $this->_actionAbstract = new \Magento\Core\Controller\Varien\Action\Forward($this->_request, $this->_response);
    }

    /**
     * Test for getRequest method
     *
     * @test
     * @covers \Magento\Core\Controller\Varien\AbstractAction::getRequest
     */
    public function testGetRequest()
    {
        $this->assertEquals($this->_request, $this->_actionAbstract->getRequest());
    }

    /**
     * Test for getResponse method
     *
     * @test
     * @covers \Magento\Core\Controller\Varien\AbstractAction::getResponse
     */
    public function testGetResponse()
    {
        $this->assertEquals($this->_response, $this->_actionAbstract->getResponse());
    }

    /**
     * Test for getResponse med. Checks that response headers are set correctly
     *
     * @test
     * @covers \Magento\Core\Controller\Varien\AbstractAction::getResponse
     */
    public function testResponseHeaders()
    {
        $eventManager = $this->getMock('Magento\Core\Model\Event\Manager', array(), array(), '', false);

        $storeManager = $this->getMock('Magento\Core\Model\StoreManager', array(), array(), '', false);
        $helperMock = $this->getMock('Magento\Backend\Helper\DataProxy', array(), array(), '', false);
        $request = new \Magento\Core\Controller\Request\Http($storeManager, $helperMock);
        $response = new \Magento\Core\Controller\Response\Http($eventManager);
        $response->headersSentThrowsException = false;
        $action = new \Magento\Core\Controller\Varien\Action\Forward($request, $response);

        $headers = array(
            array(
                'name' => 'X-Frame-Options',
                'value' => 'SAMEORIGIN',
                'replace' => false
            )
        );

        $this->assertEquals($headers, $action->getResponse()->getHeaders());
    }

    /**
     * Test for getFullActionName method
     *
     * @test
     * @covers \Magento\Core\Controller\Varien\AbstractAction::getFullActionName
     */
    public function testGetFullActionName()
    {
        $this->_request->expects($this->once())
            ->method('getRequestedRouteName')
            ->will($this->returnValue('adminhtml'));

        $this->_request->expects($this->once())
            ->method('getRequestedControllerName')
            ->will($this->returnValue('index'));

        $this->_request->expects($this->once())
            ->method('getRequestedActionName')
            ->will($this->returnValue('index'));

        $this->assertEquals('adminhtml_index_index', $this->_actionAbstract->getFullActionName());
    }
}
