<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Theme
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test theme config model
 */
class Mage_Theme_Model_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_themeMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configData;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configCacheMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutCacheMock;

    /**
     * @var Mage_Core_Model_Config_Storage_WriterInterface
     */
    protected $_storeConfigWriter;

    /**
     * @var Mage_Theme_Model_Config
     */
    protected $_model;

    protected function setUp()
    {
        /** @var $this->_themeMock Mage_Core_Model_Theme */
        $this->_themeMock = $this->getMock('Mage_Core_Model_Theme', array(), array(), '', false);
        $this->_storeManagerMock = $this->getMockForAbstractClass(
            'Mage_Core_Model_StoreManagerInterface', array(), '', true, true, true,
            array('getStores', 'isSingleStoreMode')
        );
        $this->_configData = $this->getMock(
            'Mage_Core_Model_Config_Data', array('getCollection', 'addFieldToFilter'), array(), '', false
        );
        $this->_configCacheMock = $this->getMockForAbstractClass('Magento_Cache_FrontendInterface');
        $this->_layoutCacheMock = $this->getMockForAbstractClass('Magento_Cache_FrontendInterface');

        $this->_storeConfigWriter = $this->getMock(
            'Mage_Core_Model_Config_Storage_WriterInterface', array('save', 'delete')
        );

        $this->_model = new Mage_Theme_Model_Config(
            $this->_configData,
            $this->_storeConfigWriter,
            $this->_storeManagerMock,
            $this->getMock('Mage_Core_Model_Event_Manager', array(), array(), '', false),
            $this->_configCacheMock,
            $this->_layoutCacheMock
        );
    }

    protected function tearDown()
    {
        $this->_themeMock        = null;
        $this->_configData       = null;
        $this->_themeFactoryMock = null;
        $this->_configCacheMock  = null;
        $this->_layoutCacheMock  = null;
        $this->_model            = null;
    }

    /**
     * @covers Mage_Theme_Model_Config::assignToStore
     */
    public function testAssignToStoreInSingleStoreMode()
    {
        $this->_storeManagerMock->expects($this->once())
            ->method('isSingleStoreMode')
            ->will($this->returnValue(true));

        /** Unassign themes from store */
        $configEntity = new Varien_Object(array('value' => 6, 'scope_id' => 8));

        $this->_configData->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($this->_configData));

        $this->_configData->expects($this->at(1))
            ->method('addFieldToFilter')
            ->with('scope', Mage_Core_Model_Config::SCOPE_STORES)
            ->will($this->returnValue($this->_configData));

        $this->_configData->expects($this->at(2))
            ->method('addFieldToFilter')
            ->with('path', Mage_Core_Model_View_Design::XML_PATH_THEME_ID)
            ->will($this->returnValue(array($configEntity)));

        $this->_themeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(6));

        $this->_storeConfigWriter->expects($this->once())
            ->method('delete');

        $this->_storeConfigWriter->expects($this->once())
            ->method('save');

        $this->_model->assignToStore($this->_themeMock, array(2, 3, 5));
    }

    /**
     * @covers Mage_Theme_Model_Config::assignToStore
     */
    public function testAssignToStoreNonSingleStoreMode()
    {
        $this->_storeManagerMock->expects($this->once())
            ->method('isSingleStoreMode')
            ->will($this->returnValue(false));

        /** Unassign themes from store */
        $configEntity = new Varien_Object(array('value' => 6, 'scope_id' => 8));

        $this->_configData->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($this->_configData));

        $this->_configData->expects($this->at(1))
            ->method('addFieldToFilter')
            ->with('scope', Mage_Core_Model_Config::SCOPE_STORES)
            ->will($this->returnValue($this->_configData));

        $this->_configData->expects($this->at(2))
            ->method('addFieldToFilter')
            ->with('path', Mage_Core_Model_View_Design::XML_PATH_THEME_ID)
            ->will($this->returnValue(array($configEntity)));

        $this->_themeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(6));

        $this->_storeConfigWriter->expects($this->once())
            ->method('delete');

        $this->_storeConfigWriter->expects($this->exactly(3))
            ->method('save');

        $this->_model->assignToStore($this->_themeMock, array(2, 3, 5));
    }
}
