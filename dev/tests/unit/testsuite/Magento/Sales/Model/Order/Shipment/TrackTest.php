<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Sales
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Sales\Model\Order\Shipment;

class TrackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Shipment\Track
     */
    protected $_model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject('Magento\Sales\Model\Order\Shipment\Track');
    }

    public function testAddData()
    {
        $number = 123;
        $this->assertNull($this->_model->getTrackNumber());
        $this->_model->addData(array(
            'number' => $number,
            'test' => true
        ));

        $this->assertTrue($this->_model->getTest());
        $this->assertEquals($number, $this->_model->getTrackNumber());
    }

    public function testGetStoreId()
    {
        $storeId = 10;
        $storeObject = new \Magento\Object(
            array('id' => $storeId)
        );

        $shipmentMock = $this->getMock('Magento\Sales\Model\Order\Shipment', array('getStore'), array(), '', false);
        $shipmentMock->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($storeObject));

        $this->_model->setShipment($shipmentMock);
        $this->assertEquals($storeId, $this->_model->getStoreId());
    }

    public function testSetGetNumber()
    {
        $this->assertNull($this->_model->getNumber());
        $this->assertNull($this->_model->getTrackNumber());

        $this->_model->setNumber('test');

        $this->assertEquals('test', $this->_model->getNumber());
        $this->assertEquals('test', $this->_model->getTrackNumber());
    }

    /**
     * @dataProvider isCustomDataProvider
     * @param bool $expectedResult
     * @param string $carrierCodeToSet
     */
    public function testIsCustom($expectedResult, $carrierCodeToSet)
    {
        $this->_model->setCarrierCode($carrierCodeToSet);
        $this->assertEquals($expectedResult, $this->_model->isCustom());
    }

    /**
     * @return array
     */
    public static function isCustomDataProvider()
    {
        return array(
            array(true, \Magento\Sales\Model\Order\Shipment\Track::CUSTOM_CARRIER_CODE),
            array(false, 'ups'),
        );
    }
}
