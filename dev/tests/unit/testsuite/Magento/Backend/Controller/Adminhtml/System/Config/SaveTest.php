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

class Magento_Backend_Controller_Adminhtml_System_Config_SaveTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Backend_Controller_Adminhtml_System_Config_Save
     */
    protected $_controller;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sessionMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_authMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sectionMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    public function setUp()
    {
        $this->_requestMock = $this->getMock('Magento_Core_Controller_Request_Http', array(), array(), '', false, false);
        $responseMock = $this->getMock('Magento_Core_Controller_Response_Http', array(), array(), '', false, false);

        $configStructureMock = $this->getMock('Magento_Backend_Model_Config_Structure',
            array(), array(), '', false, false
        );
        $this->_configFactoryMock = $this->getMock('Magento_Backend_Model_Config_Factory',
            array(), array(), '', false, false
        );
        $this->_eventManagerMock = $this->getMock('Magento_Core_Model_Event_Manager', array(), array(), '', false, false);

        $helperMock = $this->getMock('Magento_Backend_Helper_Data', array(), array(), '', false, false);
        $this->_sessionMock = $this->getMock('Magento_Backend_Model_Session',
            array('addSuccess', 'addException'), array(), '', false, false
        );

        $this->_authMock = $this->getMock('Magento_Backend_Model_Auth_Session',
            array('getUser'), array(), '', false, false
        );

        $this->_sectionMock = $this->getMock(
            'Magento_Backend_Model_Config_Structure_Element_Section', array(), array(), '', false
        );

        $this->_cacheMock = $this->getMockForAbstractClass('Magento_Cache_FrontendInterface');

        $configStructureMock->expects($this->any())->method('getElement')
            ->will($this->returnValue($this->_sectionMock));

        $helperMock->expects($this->any())->method('getUrl')->will($this->returnArgument(0));
        $responseMock->expects($this->once())->method('setRedirect')->with('*/system_config/edit');

        $helper = new Magento_Test_Helper_ObjectManager($this);
        $arguments = array(
            'request' => $this->_requestMock,
            'response' => $responseMock,
            'session' => $this->_sessionMock,
            'helper' => $helperMock,
            'eventManager' => $this->_eventManagerMock,
        );

        $context = $helper->getObject('Magento_Backend_Controller_Context', $arguments);
        $this->_controller = $this->getMock(
            'Magento_Backend_Controller_Adminhtml_System_Config_Save',
            array('deniedAction'),
            array(
                $context,
                $configStructureMock,
                $this->_configFactoryMock,
                $this->_authMock,
                $this->_cacheMock,
                null,
            )
        );
    }

    public function testIndexActionWithAllowedSection()
    {
        $this->_sectionMock->expects($this->any())->method('isAllowed')->will($this->returnValue(true));
        $this->_sessionMock->expects($this->once())->method('addSuccess')->with('You saved the configuration.');

        $groups = array('some_key' => 'some_value');
        $requestParamMap = array(
            array('section', null, 'test_section'),
            array('website', null, 'test_website'),
            array('store', null, 'test_store'),
        );

        $requestPostMap = array(
            array('groups', null, $groups),
            array('config_state', null, 'test_config_state'),
        );

        $this->_requestMock->expects($this->any())->method('getPost')->will($this->returnValueMap($requestPostMap));
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($requestParamMap));

        $backendConfigMock = $this->getMock('Magento_Backend_Model_Config', array(), array(), '', false, false);
        $backendConfigMock->expects($this->once())->method('save');

        $params = array('section' => 'test_section',
            'website' => 'test_website',
            'store' => 'test_store',
            'groups' => $groups
        );
        $this->_configFactoryMock->expects($this->once())->method('create')->with(array('data' => $params))
            ->will($this->returnValue($backendConfigMock));

        $this->_controller->indexAction();
    }

    public function testIndexActionWithNotAllowedSection()
    {
        $this->_sectionMock->expects($this->any())->method('isAllowed')->will($this->returnValue(false));

        $backendConfigMock = $this->getMock('Magento_Backend_Model_Config', array(), array(), '', false, false);
        $backendConfigMock->expects($this->never())->method('save');
        $this->_eventManagerMock->expects($this->never())->method('dispatch');
        $this->_sessionMock->expects($this->never())->method('addSuccess');
        $this->_sessionMock->expects($this->once())->method('addException');

        $this->_configFactoryMock->expects($this->any())->method('create')
            ->will($this->returnValue($backendConfigMock));

        $this->_controller->indexAction();
    }

    public function testIndexActionSaveState()
    {
        $this->_sectionMock->expects($this->any())->method('isAllowed')->will($this->returnValue(false));
        $data = array('some_key' => 'some_value');

        $userMock = $this->getMock('Magento_User_Model_User', array(), array(), '', false, false);
        $userMock->expects($this->once())->method('saveExtra')->with(array('configState' => $data));
        $this->_authMock->expects($this->once())->method('getUser')->will($this->returnValue($userMock));

        $this->_requestMock->expects($this->any())
            ->method('getPost')->with('config_state')->will($this->returnValue($data));
        $this->_controller->indexAction();
    }

    public function testIndexActionGetGroupForSave()
    {
        $this->_sectionMock->expects($this->any())->method('isAllowed')->will($this->returnValue(true));

        $fixturePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR;
        $groups = require_once($fixturePath . 'groups_array.php');
        $requestParamMap = array(
            array('section', null, 'test_section'),
            array('website', null, 'test_website'),
            array('store', null, 'test_store'),
        );

        $requestPostMap = array(
            array('groups', null, $groups),
            array('config_state', null, 'test_config_state'),
        );

        $files = require_once($fixturePath . 'files_array.php');

        $this->_requestMock->expects($this->any())->method('getPost')->will($this->returnValueMap($requestPostMap));
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($requestParamMap));
        $this->_requestMock->expects($this->once())
            ->method('getFiles')
            ->with('groups')
            ->will($this->returnValue($files));

        $groupToSave = require_once($fixturePath . 'expected_array.php');

        $params = array('section' => 'test_section',
            'website' => 'test_website',
            'store' => 'test_store',
            'groups' => $groupToSave
        );
        $backendConfigMock = $this->getMock('Magento_Backend_Model_Config', array(), array(), '', false, false);
        $this->_configFactoryMock->expects($this->once())->method('create')->with(array('data' => $params))
            ->will($this->returnValue($backendConfigMock));
        $backendConfigMock->expects($this->once())->method('save');

        $this->_controller->indexAction();
    }

    public function testIndexActionSaveAdvanced()
    {
        $this->_sectionMock->expects($this->any())->method('isAllowed')->will($this->returnValue(true));

        $requestParamMap = array(
            array('section', null, 'advanced'),
            array('website', null, 'test_website'),
            array('store', null, 'test_store'),
        );

        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($requestParamMap));

        $backendConfigMock = $this->getMock('Magento_Backend_Model_Config', array(), array(), '', false, false);
        $this->_configFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($backendConfigMock));
        $backendConfigMock->expects($this->once())->method('save');

        $this->_cacheMock->expects($this->once())
            ->method('clean')
            ->with(Zend_Cache::CLEANING_MODE_ALL);
        $this->_controller->indexAction();
    }
}
