<?php
/**
 * Mage_Webhook_Model_Event
 *
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Webhook_Model_EventTest extends PHPUnit_Framework_TestCase
{
    /**
     * A string used for testing time formats.  Any string will do but it should look something like this.
     */
    const SOME_FORMATTED_TIME = '2013-07-10 12:35:28';

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mockContext;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Mage_Webhook_Model_Event
     */
    protected $_event;

    public function setUp()
    {
        $this->_mockContext = $this->getMockBuilder('Mage_Core_Model_Context')
            ->disableOriginalConstructor()
            ->getMock();

        $mockEventManager = $this->getMockBuilder('Mage_Core_Model_Event_Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockContext->expects($this->once())
            ->method('getEventDispatcher')
            ->will($this->returnValue($mockEventManager));

        $this->_event = $this->getMockBuilder('Mage_Webhook_Model_Event')
            ->setConstructorArgs(array($this->_mockContext))
            ->setMethods(
                array('_init', 'isDeleted', 'isObjectNew', 'getId', '_hasModelChanged', '_getResource')
            )
            ->getMock();
    }

    public function testBeforeSaveNewObject()
    {
        $this->_mockMethodsForSave();

        $this->_event->expects($this->any())
            ->method('isObjectNew')
            ->withAnyParameters()
            ->will($this->returnValue(true));

        $mockResource = $this->getMockBuilder('Mage_Webhook_Model_Resource_Event')
            ->disableOriginalConstructor()
            ->getMock();

        $mockResource->expects($this->any())
            ->method('formatDate')
            ->with($this->equalTo(true))
            ->will($this->returnValue(self::SOME_FORMATTED_TIME));

        // needed for 'save' method
        $mockResource->expects($this->once())
            ->method('addCommitCallback')
            ->withAnyParameters()
            ->will($this->returnSelf());

        $this->_event->expects($this->any())
            ->method('_getResource')
            ->withAnyParameters()
            ->will($this->returnValue($mockResource));

        $this->assertSame($this->_event, $this->_event->save());

        $this->assertSame(self::SOME_FORMATTED_TIME, $this->_event->getCreatedAt());
        $this->assertNull($this->_event->getUpdatedAt());
        $this->assertSame(Magento_PubSub_EventInterface::READY_TO_SEND, $this->_event->getStatus());
    }

    /**
     * This method mocks all the calls required in the "save" method, such that 'beforeSave' will be called
     */
    protected function _mockMethodsForSave()
    {
        $this->_event->expects($this->once())
            ->method('isDeleted')
            ->withAnyParameters()
            ->will($this->returnValue(false));

        $this->_event->expects($this->once())
            ->method('_hasModelChanged')
            ->withAnyParameters()
            ->will($this->returnValue(true));
    }

    public function testBeforeSaveOldObject()
    {
        $this->_mockMethodsForSave();

        $this->_event->expects($this->any())
            ->method('isObjectNew')
            ->withAnyParameters()
            ->will($this->returnValue(false));

        $this->_event->expects($this->any())
            ->method('getId')
            ->withAnyParameters()
            ->will($this->returnValue(true));

        $mockResource = $this->getMockBuilder('Mage_Webhook_Model_Resource_Event')
            ->disableOriginalConstructor()
            ->getMock();
        $mockResource->expects($this->any())
            ->method('formatDate')
            ->with($this->equalTo(true))
            ->will($this->returnValue(self::SOME_FORMATTED_TIME));

        // needed for 'save' method
        $mockResource->expects($this->once())
            ->method('addCommitCallback')
            ->withAnyParameters()
            ->will($this->returnSelf());

        $this->_event->expects($this->any())
            ->method('_getResource')
            ->withAnyParameters()
            ->will($this->returnValue($mockResource));

        $this->assertSame($this->_event, $this->_event->save());

        $this->assertSame(self::SOME_FORMATTED_TIME, $this->_event->getUpdatedAt());
        $this->assertNull($this->_event->getCreatedAt());
        $this->assertSame(Magento_PubSub_EventInterface::READY_TO_SEND, $this->_event->getStatus());
    }

    public function testGettersAndSetters()
    {
        $this->assertEquals(array(), $this->_event->getBodyData());
        $data = array('some', 'random', 'data');
        $this->_event->setBodyData($data);
        $this->assertTrue($this->_event->hasDataChanges());
        $this->assertEquals($data, $this->_event->getBodyData());

        $this->assertEquals(array(), $this->_event->getHeaders());
        $this->_event->setHeaders($data);
        $this->assertTrue($this->_event->hasDataChanges());
        $this->assertEquals($data, $this->_event->getHeaders());

        $this->assertSame(Magento_PubSub_EventInterface::READY_TO_SEND, $this->_event->getStatus());
        $this->_event->setStatus($data);
        $this->assertTrue($this->_event->hasDataChanges());
        $this->assertEquals($data, $this->_event->getStatus());

        $this->assertNull($this->_event->getTopic());
        $this->_event->setTopic($data);
        $this->assertTrue($this->_event->hasDataChanges());
        $this->assertEquals($data, $this->_event->getTopic());
    }
}