<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Log\Model;

class ShellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shellMock;

    /**
     * @var \Magento\Log\Model\Shell
     */
    protected $_model;

    protected function setUp()
    {
        $this->_factoryMock = $this->getMock('Magento\Log\Model\Shell\Command\Factory', array(), array(), '', false);
        $filesystemMock = $this->getMock('Magento\Filesystem', array(), array(), '', false);
        $dirMock = $this->getMock('Magento\Core\Model\Dir', array(), array(), '', false);
        $this->_model = $this->getMock('Magento\Log\Model\Shell',
            array('_applyPhpVariables'),
            array($this->_factoryMock, $filesystemMock, 'entryPoint.php', $dirMock)
        );
    }

    public function testRunWithShowHelp()
    {
        $this->expectOutputRegex('/Usage\:  php -f entryPoint\.php/');
        $this->_model->setRawArgs(array('h'));
        $this->_factoryMock->expects($this->never())->method('createCleanCommand');
        $this->_factoryMock->expects($this->never())->method('createStatusCommand');
        $this->_model->run();
    }

    public function testRunWithCleanCommand()
    {
        $this->expectOutputRegex('/clean command message/');
        $this->_model->setRawArgs(array('clean', '--days', 10));
        $commandMock = $this->getMock('Magento\Log\Model\Shell\CommandInterface');
        $this->_factoryMock->expects($this->once())
            ->method('createCleanCommand')
            ->with(10)
            ->will($this->returnValue($commandMock));
        $commandMock->expects($this->once())->method('execute')->will($this->returnValue('clean command message'));
        $this->_factoryMock->expects($this->never())->method('createStatusCommand');
        $this->_model->run();
    }

    public function testRunWithStatusCommand()
    {
        $this->expectOutputRegex('/status command message/');
        $this->_model->setRawArgs(array('status'));
        $commandMock = $this->getMock('Magento\Log\Model\Shell\CommandInterface');
        $this->_factoryMock->expects($this->once())
            ->method('createStatusCommand')
            ->will($this->returnValue($commandMock));
        $commandMock->expects($this->once())->method('execute')->will($this->returnValue('status command message'));
        $this->_factoryMock->expects($this->never())->method('createCleanCommand');
        $this->_model->run();
    }

    public function testRunWithoutCommand()
    {
        $this->expectOutputRegex('/Usage\:  php -f entryPoint\.php/');
        $this->_factoryMock->expects($this->never())->method('createStatusCommand');
        $this->_factoryMock->expects($this->never())->method('createCleanCommand');
        $this->_model->run();
    }
}