<?php
/**
 * Test class for Mage_Core_Model_Dataservice_Factory
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Core_Model_Dataservice_FactoryTest extends PHPUnit_Framework_TestCase
{
    const TEST_CLASS_NAME = 'TEST_CLASS_NAME';

    const TEST_DATA_SERVICE_NAME = 'TEST_DATA_SERVICE_NAME';

    const TEST_NAMESPACE = 'TEST_NAMESPACE';

    const TEST_NAMESPACE_ALIAS = 'TEST_NAMESPACE_ALIAS';

    /** @var Mage_Core_Model_Dataservice_Factory */
    protected $_factory;

    /** @var Mage_Core_Model_Dataservice_Config_Interface */
    protected $_configMock;

    /** @var  Magento_ObjectManager */
    protected $_objectManagerMock;

    /** @var  Mage_Core_Model_Dataservice_Path_Composite */
    protected $_compositeMock;

    /** @var  Mage_Core_Model_Dataservice_Path_Navigator */
    protected $_pathNavigatorMock;

    /** @var  Mage_Core_Model_Dataservice_Repository */
    protected $_repositoryMock;

    protected $_dataserviceMock;

    public function retrieveMethod()
    {
        return $this->_dataserviceMock;
    }

    public function setup()
    {
        $this->_configMock = $this->getMock(
            'Mage_Core_Model_Dataservice_Config_Interface', array(), array(), "", false
        );
        $this->_objectManagerMock = $this->getMock('Magento_ObjectManager', array(), array(), "", false);
        $this->_compositeMock = $this->getMock(
            'Mage_Core_Model_Dataservice_Path_Composite', array(), array(), "", false
        );
        $this->_pathNavigatorMock = $this->getMockBuilder('Mage_Core_Model_Dataservice_Path_Navigator')
            ->disableOriginalConstructor()->getMock();
        $this->_repositoryMock = $this->getMock('Mage_Core_Model_Dataservice_Repository', array(), array(), "", false);
        $this->_factory = new Mage_Core_Model_Dataservice_Factory(
            $this->_configMock,
            $this->_objectManagerMock,
            $this->_compositeMock,
            $this->_pathNavigatorMock,
            $this->_repositoryMock);
        $this->_dataserviceMock = (object)array();
    }

    public function testInit()
    {
        $this->_repositoryMock->expects($this->once())->method('addNameInNamespace')->with(
            self::TEST_NAMESPACE,
            self::TEST_DATA_SERVICE_NAME,
            self::TEST_NAMESPACE_ALIAS
        );
        $namespaceConfig
            = array('namespaces' => array(self::TEST_NAMESPACE =>
                                          Mage_Core_Model_Dataservice_FactoryTest::TEST_NAMESPACE_ALIAS));
        $this->_repositoryMock->expects($this->once())->method("get")->with(
            $this->equalTo(self::TEST_DATA_SERVICE_NAME)
        )->will($this->returnValue(null));
        $classInformation = array('class'          => self::TEST_CLASS_NAME,
                                  'retrieveMethod' => 'retrieveMethod', 'methodArguments' => array());
        $this->_configMock->expects($this->once())->method("getClassByAlias")->with(
            $this->equalTo(self::TEST_DATA_SERVICE_NAME)
        )->will($this->returnValue($classInformation));
        $this->_objectManagerMock->expects($this->once())->method("create")->with(
            $this->equalTo(self::TEST_CLASS_NAME)
        )->will($this->returnValue($this));
        $this->_repositoryMock->expects($this->once())->method("add")->with(
            $this->equalTo(self::TEST_DATA_SERVICE_NAME),
            $this->equalTo($this->_dataserviceMock)
        );
        $this->_factory->init(
            array(self::TEST_DATA_SERVICE_NAME => $namespaceConfig)
        );
    }

    public function testGet()
    {
        $this->_dataserviceMock = (object)array();
        $this->_repositoryMock->expects($this->at(0))->method("get")->with(
            $this->equalTo(self::TEST_DATA_SERVICE_NAME)
        )->will($this->returnValue(null));
        $this->_repositoryMock->expects($this->at(1))->method("get")->with(
            $this->equalTo(self::TEST_DATA_SERVICE_NAME)
        )->will($this->returnValue($this->_dataserviceMock));
        $this->assertEquals(
            $this->_dataserviceMock,
            $this->_factory->get(self::TEST_DATA_SERVICE_NAME)
        );
    }

    public function testGetByNamespace()
    {
        $this->_repositoryMock->expects($this->once())->method('getByNamespace')->with(
            self::TEST_NAMESPACE
        )->will($this->returnValue(self::TEST_DATA_SERVICE_NAME));
        $this->assertEquals(
            self::TEST_DATA_SERVICE_NAME,
            $this->_factory->getByNamespace(self::TEST_NAMESPACE)
        );
    }

    public function testGetArgumentValue()
    {
        $path = 'path';
        $result = 'result';
        $pathArray = array($path);
        $this->_pathNavigatorMock->expects($this->once())->method('search')->with($this->_compositeMock, $pathArray)->will(
            $this->returnValue($result)
        );
        $this->assertEquals($result, $this->_factory->getArgumentValue($path));
    }
}