<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Log\Model\Shell\Command;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Log\Model\Shell\Command\Factory
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\ObjectManager');
        $this->_model = new \Magento\Log\Model\Shell\Command\Factory($this->_objectManagerMock);
    }

    public function testCreateCleanCommand()
    {
        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Log\Model\Shell\Command\Clean', array('days' => 1))
            ->will($this->returnValue(
                $this->getMock('Magento\Log\Model\Shell\Command\Clean', array(), array(), '', false)
            )
        );
        $this->isInstanceOf('Magento\Log\Model\Shell\CommandInterface', $this->_model->createCleanCommand(1));
    }

    public function testCreateStatusCommand()
    {
        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Log\Model\Shell\Command\Status')
            ->will($this->returnValue(
                $this->getMock('Magento\Log\Model\Shell\Command\Status', array(), array(), '', false)
            )
        );
        $this->isInstanceOf('Magento\Log\Model\Shell\CommandInterface', $this->_model->createStatusCommand());
    }
}
