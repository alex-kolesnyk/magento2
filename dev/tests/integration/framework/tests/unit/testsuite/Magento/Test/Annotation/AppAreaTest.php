<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Magento_Test_Annotation_AppAreaTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Test_Annotation_AppArea
     */
    protected $_object;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_applicationMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_testCaseMock;

    protected function setUp()
    {
        $this->_testCaseMock = $this->getMock('PHPUnit_Framework_TestCase', array(), array(), '', false);
        $this->_applicationMock = $this->getMock('Magento_Test_Application', array(), array(), '', false);
        $this->_object = new Magento_Test_Annotation_AppArea($this->_applicationMock);
    }

    /**
     * @param array $annotations
     * @param string $expectedArea
     * @dataProvider getTestAppAreaDataProvider
     */
    public function testGetTestAppArea($annotations, $expectedArea)
    {
        $this->_testCaseMock->expects($this->once())->method('getAnnotations')->will($this->returnValue($annotations));
        $this->_applicationMock->expects($this->any())->method('getArea')->will($this->returnValue(null));
        $this->_applicationMock->expects($this->once())->method('reinitialize');
        $this->_applicationMock->expects($this->once())->method('loadArea')->with($expectedArea);
        $this->_object->startTest($this->_testCaseMock);
    }

    public function getTestAppAreaDataProvider()
    {
        return array(
            'method scope' => array(
                array('method' => array('magentoAppArea' => array('adminhtml'))), 'adminhtml'
            ),
            'class scope' => array(
                array('class' => array('magentoAppArea' => array('frontend'))), 'frontend'
            ),
            'mixed scope' => array(
                array(
                    'class'  => array('magentoAppArea' => array('adminhtml')),
                    'method' => array('magentoAppArea' => array('frontend')),
                ), 'frontend'
            ),
            'default area' => array(
                array(), 'global'
            ),
        );
    }

    /**
     * @expectedException Magento_Exception
     */
    public function testGetTestAppAreaWithInvalidArea()
    {
        $annotations =  array('method' => array('magentoAppArea' => array('some_invalid_area')));
        $this->_testCaseMock->expects($this->once())->method('getAnnotations')->will($this->returnValue($annotations));
        $this->_object->startTest($this->_testCaseMock);
    }

    public function testStartTestPreventDoubleAreaLoadingAfterReinitialization()
    {
        $annotations =  array('method' => array('magentoAppArea' => array('global')));
        $this->_testCaseMock->expects($this->once())->method('getAnnotations')->will($this->returnValue($annotations));
        $this->_applicationMock->expects($this->at(0))->method('getArea')->will($this->returnValue('adminhtml'));
        $this->_applicationMock->expects($this->once())->method('reinitialize');
        $this->_applicationMock->expects($this->at(2))->method('getArea')->will($this->returnValue('global'));
        $this->_applicationMock->expects($this->never())->method('loadArea');
        $this->_object->startTest($this->_testCaseMock);
    }

    public function testStartTestPreventDoubleAreaLoading()
    {
        $annotations =  array('method' => array('magentoAppArea' => array('adminhtml')));
        $this->_testCaseMock->expects($this->once())->method('getAnnotations')->will($this->returnValue($annotations));
        $this->_applicationMock->expects($this->once())->method('getArea')->will($this->returnValue('adminhtml'));
        $this->_applicationMock->expects($this->never())->method('reinitialize');
        $this->_applicationMock->expects($this->never())->method('loadArea');
        $this->_object->startTest($this->_testCaseMock);
    }
}