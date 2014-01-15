<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\GoogleShopping\Model;


class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_helper;

    /**
     * @var \Magento\GoogleShopping\Model\Service
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_contentMock;

    protected function setUp()
    {
        $this->_helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_contentMock = $this->getMockBuilder('Magento\Gdata\Gshopping\Content')
            ->disableOriginalConstructor()
            ->getMock();
        $contentFactoryMock = $this->getMockBuilder('Magento\Gdata\Gshopping\ContentFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $contentFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_contentMock));

        $coreRegistryMock = $this->getMockBuilder('Magento\Core\Model\Registry')
            ->disableOriginalConstructor()
            ->setMethods(array('registry'))
            ->getMock();
        $coreRegistryMock->expects($this->any())
            ->method('registry')
            ->will($this->returnValue(1));

        $arguments = array(
            'contentFactory' => $contentFactoryMock,
            'coreRegistry' => $coreRegistryMock
        );
        $this->_model = $this->_helper->getObject('\Magento\GoogleShopping\Model\Service', $arguments);
    }

    public function testGetService()
    {
        $this->assertEquals('Magento\Gdata\Gshopping\Content', get_parent_class($this->_model->getService()));
    }

    public function testSetService()
    {
        $this->_model->setService($this->_contentMock);
        $this->assertSame($this->_contentMock, $this->_model->getService());
    }
}
