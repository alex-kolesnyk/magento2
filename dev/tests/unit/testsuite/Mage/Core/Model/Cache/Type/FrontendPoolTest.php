<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Core_Model_Cache_Type_FrontendPoolTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Cache_Type_FrontendPool
     */
    protected $_model;

    /**
     * @var Magento_ObjectManager_Zend|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_Cache_Frontend_Pool|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cachePool;

    public function setUp()
    {
        $this->_objectManager = $this->getMock('Magento_ObjectManager_Zend', array(), array(), '', false);
        $this->_cachePool = $this->getMock('Mage_Core_Model_Cache_Frontend_Pool', array(), array(), '', false);
        $this->_model = new Mage_Core_Model_Cache_Type_FrontendPool($this->_objectManager, $this->_cachePool);
    }

    public function testGet()
    {
        $instanceMock = $this->getMock('Magento_Cache_FrontendInterface');
        $this->_cachePool->expects($this->once())
            ->method('get')
            ->with('cache_type')
            ->will($this->returnValue($instanceMock));

        $accessMock = $this->getMock('Mage_Core_Model_Cache_Type_AccessProxy', array(), array(), '', false);
        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with('Mage_Core_Model_Cache_Type_AccessProxy',
                array('frontend' => $instanceMock, 'identifier' => 'cache_type'))
            ->will($this->returnValue($accessMock));

        $instance = $this->_model->get('cache_type');
        $this->assertSame($accessMock, $instance);

        // And must be cached
        $instance = $this->_model->get('cache_type');
        $this->assertSame($accessMock, $instance);
    }

    public function testGetFallbackToDefaultId()
    {
        $instanceMock = $this->getMock('Magento_Cache_FrontendInterface');
        $this->_cachePool->expects($this->at(0))
            ->method('get')
            ->with('cache_type')
            ->will($this->returnValue(null));
        $this->_cachePool->expects($this->at(1))
            ->method('get')
            ->with(Mage_Core_Model_Cache_Frontend_Pool::DEFAULT_FRONTEND_ID)
            ->will($this->returnValue($instanceMock));

        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with('Mage_Core_Model_Cache_Type_AccessProxy',
                array('frontend' => $instanceMock, 'identifier' => 'cache_type'))
            ->will($this->returnValue(
                $this->getMock('Mage_Core_Model_Cache_Type_AccessProxy', array(), array(), '', false)
            ));

        $instance = $this->_model->get('cache_type');
    }
}
