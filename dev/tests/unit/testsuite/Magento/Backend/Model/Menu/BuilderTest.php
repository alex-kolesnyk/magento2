<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Backend
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */


namespace Magento\Backend\Model\Menu;

class BuilderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Backend\Model\Menu\Builder
     */
    protected  $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_menuMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    protected function setUp()
    {
        $this->_factoryMock = $this->getMock("Magento\Backend\Model\Menu\Item\Factory", array(), array(), '', false);
        $this->_menuMock = $this->getMock('Magento\Backend\Model\Menu', array(),
            array($this->getMock('Magento\Logger', array(), array(), '', false)));

        $this->_model = new \Magento\Backend\Model\Menu\Builder($this->_factoryMock, $this->_menuMock);
    }

    public function testProcessCommand()
    {
        $command = $this->getMock('Magento\Backend\Model\Menu\Builder\Command\Add', array(), array(), '', false);
        $command->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $command2 = $this->getMock('Magento\Backend\Model\Menu\Builder\Command\Update', array(), array(), '', false);
        $command2->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $command->expects($this->once())
            ->method('chain')
            ->with($this->equalTo($command2));
        $this->_model->processCommand($command);
        $this->_model->processCommand($command2);
    }

    public function testGetResultBuildsTreeStructure()
    {
        $item1 = $this->getMock("Magento\Backend\Model\Menu\Item", array(), array(), '', false);
        $item1->expects($this->once())->method('getChildren')->will($this->returnValue($this->_menuMock));
        $this->_factoryMock->expects($this->any())->method('create')->will($this->returnValue($item1));

        $item2 = $this->getMock("Magento\Backend\Model\Menu\Item", array(), array(), '', false);
        $this->_factoryMock->expects($this->at(1))->method('create')->will($this->returnValue($item2));

        $this->_menuMock->expects($this->at(0))
            ->method('add')
            ->with(
            $this->isInstanceOf('Magento\Backend\Model\Menu\Item'),
            $this->equalTo(null),
            $this->equalTo(2)
        );

        $this->_menuMock->expects($this->at(1))
            ->method('add')
            ->with(
            $this->isInstanceOf('Magento\Backend\Model\Menu\Item'),
            $this->equalTo(null),
            $this->equalTo(4)
        );

        $this->_model->processCommand(
            new \Magento\Backend\Model\Menu\Builder\Command\Add(
                array(
                    'id' => 'item1',
                    'title' => 'Item 1',
                    'module' => 'Magento_Backend',
                    'sortOrder' => 2,
                    'resource' => 'Magento_Backend::item1')
            )
        );
        $this->_model->processCommand(
            new \Magento\Backend\Model\Menu\Builder\Command\Add(
                array(
                    'id' => 'item2',
                    'parent' => 'item1',
                    'title' => 'two',
                    'module' => 'Magento_Backend',
                    'sortOrder' => 4,
                    'resource' => 'Magento_Backend::item2'
                )
            )
        );

        $this->_model->getResult($this->_menuMock);
    }

    public function testGetResultSkipsRemovedItems()
    {
        $this->_model->processCommand(new \Magento\Backend\Model\Menu\Builder\Command\Add(array(
                'id' => 1,
                'title' => 'Item 1',
                'module' => 'Magento_Backend',
                'resource' => 'Magento_Backend::i1'
            )
        ));
        $this->_model->processCommand(
            new \Magento\Backend\Model\Menu\Builder\Command\Remove(
                array('id' => 1,)
            )
        );

        $this->_menuMock->expects($this->never())
            ->method('addChild');

        $this->_model->getResult($this->_menuMock);
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testGetResultSkipItemsWithInvalidParent()
    {
        $item1 = $this->getMock("Magento\Backend\Model\Menu\Item", array(), array(), '', false);
        $this->_factoryMock->expects($this->any())->method('create')
            ->will($this->returnValue($item1));

        $this->_model->processCommand(new \Magento\Backend\Model\Menu\Builder\Command\Add(array(
                'id' => 'item1',
                'parent' => 'not_exists',
                'title' => 'Item 1',
                'module' => 'Magento_Backend',
                'resource' => 'Magento_Backend::item1'
            )
        ));

        $this->_model->getResult($this->_menuMock);
    }
}
