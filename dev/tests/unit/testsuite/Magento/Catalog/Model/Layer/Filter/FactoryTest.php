<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Catalog\Model\Layer\Filter;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\Factory
     */
    protected $_factory;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\ObjectManager', array(), array(), '', false);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_factory = $objectManagerHelper->getObject('Magento\Catalog\Model\Layer\Filter\Factory', array(
            'objectManager' => $this->_objectManagerMock,
        ));
    }

    public function testCreate()
    {
        $className = 'Magento\Catalog\Model\Layer\Filter\AbstractFilter';

        $filterMock = $this->getMock($className, array(), array(), '', false);
        $this->_objectManagerMock->expects($this->once())->method('create')->with($className, array())
            ->will($this->returnValue($filterMock));

        $this->assertEquals($filterMock, $this->_factory->create($className));
    }

    public function testCreateWithArguments()
    {
        $className = 'Magento\Catalog\Model\Layer\Filter\AbstractFilter';
        $arguments = array('foo', 'bar');

        $filterMock = $this->getMock($className, array(), array(), '', false);
        $this->_objectManagerMock->expects($this->once())->method('create')->with($className, $arguments)
            ->will($this->returnValue($filterMock));

        $this->assertEquals($filterMock, $this->_factory->create($className, $arguments));
    }

    /**
     * @expectedException \Magento\Core\Exception
     * @expectedExceptionMessage WrongClass doesn't extends \Magento\Catalog\Model\Layer\Filter\AbstractFilter
     */
    public function testWrongTypeException()
    {
        $className = 'WrongClass';

        $filterMock = $this->getMock($className, array(), array(), '', false);
        $this->_objectManagerMock->expects($this->once())->method('create')->will($this->returnValue($filterMock));

        $this->_factory->create($className);
    }
}
