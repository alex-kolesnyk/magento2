<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Reports\Model\Plugin;

class LogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Model\Plugin\Log
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_reportEventMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cmpProductIdxMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_viewProductIdxMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logResourceMock;

    protected function setUp()
    {
        $this->_reportEventMock = $this->getMock(
            'Magento\Reports\Model\Event', array(), array(), '', false
        );
        $this->_cmpProductIdxMock = $this->getMock(
            'Magento\Reports\Model\Product\Index\Compared', array(), array(), '', false
        );
        $this->_viewProductIdxMock = $this->getMock(
            'Magento\Reports\Model\Product\Index\Viewed', array(), array(), '', false
        );

        $this->_logResourceMock = $this->getMock('Magento\Log\Model\Resource\Log', array(), array(), '', false);

        $this->_model = new \Magento\Reports\Model\Plugin\Log(
            $this->_reportEventMock,
            $this->_cmpProductIdxMock,
            $this->_viewProductIdxMock
        );
    }

    /**
     * @covers \Magento\Reports\Model\Plugin\Log::afterClean
     */
    public function testAfterClean()
    {
        $this->_reportEventMock->expects($this->once())
            ->method('clean');

        $this->_cmpProductIdxMock->expects($this->once())
            ->method('clean');

        $this->_viewProductIdxMock->expects($this->once())
            ->method('clean');

        $this->assertEquals($this->_logResourceMock, $this->_model->afterClean($this->_logResourceMock));
    }
}
