<?php
/**
 * Config helper Unit tests.
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Class implements tests for \Magento\Webapi\Model\Soap\Config class.
 */
class Magento_Webapi_Model_Soap_ConfigTest extends PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Model\Soap\Config */
    protected $_soapConfig;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $objectManagerMock = $this->getMockBuilder('Magento\Core\Model\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $fileSystemMock = $this->getMockBuilder('Magento\Filesystem')->disableOriginalConstructor()->getMock();
        $dirMock = $this->getMockBuilder('Magento\Core\Model\Dir')->disableOriginalConstructor()->getMock();
        $configMock = $this->getMockBuilder('Magento\Webapi\Model\Config')->disableOriginalConstructor()->getMock();
        $this->_soapConfig = new \Magento\Webapi\Model\Soap\Config(
            $objectManagerMock,
            $fileSystemMock,
            $dirMock,
            $configMock
        );
        parent::setUp();
    }

    /**
     * Test identifying service name parts including subservices using class name.
     *
     * @dataProvider serviceNamePartsDataProvider
     */
    public function testGetServiceNameParts($className, $preserveVersion, $expected)
    {
        $actual = $this->_soapConfig->getServiceNameParts(
            $className,
            $preserveVersion
        );
        $this->assertEquals($expected, $actual);
    }

    /**
     * Dataprovider for serviceNameParts
     *
     * @return array
     */
    public function serviceNamePartsDataProvider()
    {
        return array(
            array('Magento_Customer_Service_Customer_AddressV1Interface', false, array('Customer', 'Address')),
            array(
                'Vendor_Customer_Service_Customer_AddressV1Interface',
                true,
                array('VendorCustomer', 'Address', 'V1')
            ),
            array('Magento_Catalog_Service_ProductV2Interface', true, array('CatalogProduct', 'V2'))
        );
    }
}
