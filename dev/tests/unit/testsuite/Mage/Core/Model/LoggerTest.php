<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Core_Model_LoggerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Logger|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model = null;

    /**
     * @var ReflectionProperty
     */
    protected $_loggersProperty = null;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystemMock;

    protected function setUp()
    {
        $this->_dirMock = $this->getMock('Mage_Core_Model_Dir', array(), array(), '', false, false);
        $this->_filesystemMock = $this->getMock('Varien_Io_File', array(), array(), '', false, false);

        $this->_model = new Mage_Core_Model_Logger($this->_dirMock, $this->_filesystemMock, 'php://output');
        $this->_loggersProperty = new ReflectionProperty($this->_model, '_loggers');
        $this->_loggersProperty->setAccessible(true);
    }

    /**
     * @param string $key
     * @param string $fileOrWrapper
     * @dataProvider addStreamLogDataProvider
     */
    public function testAddStreamLog($key, $fileOrWrapper)
    {
        $this->assertFalse($this->_model->hasLog($key));
        $this->_model->addStreamLog($key, $fileOrWrapper);
        $this->assertTrue($this->_model->hasLog($key));

        $loggers = $this->_loggersProperty->getValue($this->_model);
        $this->assertArrayHasKey($key, $loggers);
        $zendLog = $loggers[$key];
        $this->assertInstanceOf('Zend_Log', $zendLog);

        $writersProperty = new ReflectionProperty($zendLog, '_writers');
        $writersProperty->setAccessible(true);
        $writers = $writersProperty->getValue($zendLog);
        $this->assertArrayHasKey(0, $writers);
        $stream = $writers[0];
        $this->assertInstanceOf('Zend_Log_Writer_Stream', $writers[0]);

        $streamProperty = new ReflectionProperty($stream, '_stream');
        $streamProperty->setAccessible(true);
        $fileOrWrapper = $streamProperty->getValue($stream);
        $this->assertInternalType('resource', $fileOrWrapper);
        $this->assertEquals('stream', get_resource_type($fileOrWrapper));
    }

    /**
     * @return array
     */
    public function addStreamLogDataProvider()
    {
        return array(
            array('test', 'php://output'),
            array('test', 'custom_file.log'),
            array('test', ''),
        );
    }

    public function testInitForStore()
    {
        $config = $this->getMock('Mage_Core_Model_Config', array('getNode'), array(), '', false);
        $config->expects($this->atLeastOnce())
            ->method('getNode')
            ->with('global/log/core/writer_model')
            ->will($this->returnValue('StdClass'));
        $store = $this->getMock('Mage_Core_Model_Store', array('getConfig'), array(), '', false);
        $store->expects($this->at(0))->method('getConfig')->with('dev/log/active')->will($this->returnValue(false));
        $store->expects($this->at(1))->method('getConfig')->with('dev/log/active')->will($this->returnValue(true));
        $store->expects($this->at(2))->method('getConfig')->with('dev/log/file')->will($this->returnValue(''));
        $store->expects($this->at(3))->method('getConfig')->with('dev/log/exception_file')->will(
            $this->returnValue('')
        );
        $this->_model->initForStore($store, $config);
        $this->assertFalse($this->_model->hasLog(Mage_Core_Model_Logger::LOGGER_SYSTEM));
        $this->assertFalse($this->_model->hasLog(Mage_Core_Model_Logger::LOGGER_EXCEPTION));
        $this->_model->initForStore($store, $config);
        $this->assertTrue($this->_model->hasLog(Mage_Core_Model_Logger::LOGGER_SYSTEM));
        $this->assertTrue($this->_model->hasLog(Mage_Core_Model_Logger::LOGGER_EXCEPTION));
    }

    /**
     * @covers Mage_Core_Model_Logger::hasLog
     */
    public function testAddStoreLog()
    {
        $this->_filesystemMock->expects($this->once())->method('checkAndCreateFolder');
        $store = $this->getMock('Mage_Core_Model_Store', array('getConfig'), array(), '', false);
        $store->expects($this->at(0))->method('getConfig')->with('dev/log/active')->will($this->returnValue(false));
        $store->expects($this->at(1))->method('getConfig')->with('dev/log/active')->will($this->returnValue(true));
        $key = uniqid();
        $this->_model->addStoreLog($key, $store);
        $this->assertFalse($this->_model->hasLog($key));
        $this->_model->addStoreLog($key, $store);
        $this->assertTrue($this->_model->hasLog($key));
    }

    public function testLog()
    {
        $messageOne = uniqid();
        $messageTwo = uniqid();
        $messageThree = uniqid();
        $this->expectOutputRegex('/' . 'DEBUG \(7\).+?' . $messageTwo . '.+?' . 'CRIT \(2\).+?' . $messageThree . '/s');
        $this->_model->addStreamLog('test', 'php://output');
        $this->_model->log($messageOne);
        $this->_model->log($messageTwo, Zend_Log::DEBUG, 'test');
        $this->_model->log($messageThree, Zend_Log::CRIT, 'test');
    }

    public function testLogComplex()
    {
        $this->expectOutputRegex('/Array\s\(\s+\[0\] => 1\s\).+stdClass Object/s');
        $this->_model->addStreamLog(Mage_Core_Model_Logger::LOGGER_SYSTEM, 'php://output');
        $this->_model->log(array(1));
        $this->_model->log(new StdClass);
    }

    public function testLogDebug()
    {
        $message = uniqid();
        /** @var $model Mage_Core_Model_Logger|PHPUnit_Framework_MockObject_MockObject */
        $model = $this->getMock('Mage_Core_Model_Logger', array('log'), array(), '', false);
        $model->expects($this->at(0))->method('log')
            ->with($message, Zend_Log::DEBUG, Mage_Core_Model_Logger::LOGGER_SYSTEM);
        $model->expects($this->at(1))->method('log')
            ->with($message, Zend_Log::DEBUG, Mage_Core_Model_Logger::LOGGER_EXCEPTION);
        $model->logDebug($message);
        $model->logDebug($message, Mage_Core_Model_Logger::LOGGER_EXCEPTION);
    }

    public function testLogException()
    {
        $exception = new Exception;
        $expected = "\n{$exception}";
        /** @var $model Mage_Core_Model_Logger|PHPUnit_Framework_MockObject_MockObject */
        $model = $this->getMock('Mage_Core_Model_Logger', array('log'), array(), '', false);
        $model->expects($this->at(0))->method('log')
            ->with($expected, Zend_Log::ERR, Mage_Core_Model_Logger::LOGGER_EXCEPTION);
        $model->logException($exception);
    }
}
