<?php
/**
 * SOAP API Request Test.
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Magento_Webapi_Controller_Soap_RequestTest extends PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Controller\Soap\Request */
    protected $_soapRequest;

    protected function setUp()
    {
        /** Prepare mocks for SUT constructor. */
        $applicationMock = $this->getMockBuilder('Magento\Core\Model\App')
            ->disableOriginalConstructor()
            ->getMock();
        $configMock = $this->getMockBuilder('Magento\Core\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $applicationMock->expects($this->once())->method('getConfig')->will($this->returnValue($configMock));
        $configMock->expects($this->once())->method('getAreaFrontName')->will($this->returnValue('soap'));

        /** Initialize SUT. */
        $this->_soapRequest = new \Magento\Webapi\Controller\Soap\Request($applicationMock);
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_soapRequest);
        parent::tearDown();
    }

    public function testGetRequestedServicesNotAllowedParametersException()
    {
        /** Prepare mocks for SUT constructor. */
        $wsdlParam = \Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_WSDL;
        $servicesParam = \Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_SERVICES;
        // Set two not allowed parameters and all allowed
        $requestParams = array(
            'param_1' => 'foo',
            'param_2' => 'bar',
            $wsdlParam => true,
            $servicesParam => true
        );
        $this->_soapRequest->setParams($requestParams);
        $exceptionMessage = 'Not allowed parameters: param_1, param_2. Please use only wsdl and services.';
        /** Execute SUT. */
        try {
            $this->_soapRequest->getRequestedServices();
            $this->fail("Exception is expected to be raised");
        } catch (\Magento\Webapi\Exception $e) {
            $this->assertInstanceOf('Magento\Webapi\Exception', $e, 'Exception type is invalid');
            $this->assertEquals($exceptionMessage, $e->getMessage(), 'Exception message is invalid');
            $this->assertEquals(\Magento\Webapi\Exception::HTTP_BAD_REQUEST, $e->getHttpCode(), 'HTTP code is invalid');
        }
    }

    public function testGetRequestedServicesEmptyRequestedServicesException()
    {
        /** Prepare mocks for SUT constructor. */
        $requestParams = array(\Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_SERVICES => null);
        $this->_soapRequest->setParams($requestParams);
        $exceptionMessage = 'Incorrect format of WSDL request URI or Requested services are missing.';
        /** Execute SUT. */
        try {
            $this->_soapRequest->getRequestedServices();
            $this->fail("Exception is expected to be raised");
        } catch (\Magento\Webapi\Exception $e) {
            $this->assertInstanceOf('Magento\Webapi\Exception', $e, 'Exception type is invalid');
            $this->assertEquals($exceptionMessage, $e->getMessage(), 'Exception message is invalid');
            $this->assertEquals(\Magento\Webapi\Exception::HTTP_BAD_REQUEST, $e->getHttpCode(), 'HTTP code is invalid');
        }
    }

    /**
     * @dataProvider providerTestGetRequestedServicesSuccess
     * @param $requestParamServices
     * @param $expectedResult
     */
    public function testGetRequestedServicesSuccess($requestParamServices, $expectedResult)
    {
        $requestParams = array(
            \Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_WSDL => true,
            \Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_SERVICES => $requestParamServices
        );
        $this->_soapRequest->setParams($requestParams);
        $this->assertEquals($expectedResult, $this->_soapRequest->getRequestedServices());
    }

    public function providerTestGetRequestedServicesSuccess()
    {
        $testModuleA = 'testModule1AllSoapAndRestV1';
        $testModuleB = 'testModule1AllSoapAndRestV2';
        $testModuleC = 'testModule2AllSoapNoRestV1';
        return array(
            array(
                "{$testModuleA},{$testModuleB}",
                array(
                    $testModuleA,
                    $testModuleB
                )
            ),
            array(
                "{$testModuleA},{$testModuleC}",
                array(
                    $testModuleA,
                    $testModuleC
                )
            ),
            array(
                "{$testModuleA}",
                array(
                    $testModuleA
                )
            )
        );
    }
}
