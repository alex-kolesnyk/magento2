<?php
/**
 * \Magento\Webhook\Model\Resource\Job\Collection
 *
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Magento_Webhook_Model_Resource_Job_CollectionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $mockDBAdapter = $this->getMockBuilder('Magento\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array('_connect', '_quote'))
            ->getMockForAbstractClass();
        $mockResourceEvent = $this->getMockBuilder('Magento\Webhook\Model\Resource\Job')
            ->disableOriginalConstructor()
            ->getMock();
        $mockResourceEvent->expects($this->once())
            ->method('getReadConnection')
            ->will($this->returnValue($mockDBAdapter));

        $mockObjectManager = $this->_setMageObjectManager();
        $mockObjectManager->expects($this->once())
            ->method('create')
            ->with($this->equalTo('Magento\Webhook\Model\Resource\Job'))
            ->will($this->returnValue($mockResourceEvent));
    }

    public function tearDown()
    {
        // Unsets object manager
        Mage::reset();
    }

    public function testConstructor()
    {
        $eventManager = $this->getMock('Magento\Core\Model\Event\Manager', array(), array(), '', false);
        $mockFetchStrategy = $this->getMockBuilder('Magento\Data\Collection\Db\FetchStrategyInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $collection = new \Magento\Webhook\Model\Resource\Job\Collection($eventManager, $mockFetchStrategy);
        $this->assertInstanceOf('Magento\Webhook\Model\Resource\Job\Collection', $collection);
        $this->assertEquals('Magento_Webhook_Model_Resource_Job', $collection->getResourceModelName());
    }

    /**
     * Makes sure that Mage has a mock object manager set, and returns that instance.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _setMageObjectManager()
    {
        Mage::reset();
        $mockObjectManager = $this->getMockBuilder('Magento\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        Mage::setObjectManager($mockObjectManager);

        return $mockObjectManager;
    }
}
