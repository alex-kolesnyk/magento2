<?php
/**
 * Soap API request.
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Webapi\Controller\Soap;

class Request extends \Magento\Webapi\Controller\Request
{
    /** @var \Magento\Core\Model\App */
    protected $_application;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Core\Model\App $application
     * @param string|null $uri
     */
    public function __construct(\Magento\Core\Model\App $application, $uri = null)
    {
        parent::__construct($application, $uri);
    }

    /**
     * Identify versions of resources that should be used for API configuration generation.
     * TODO : This is getting called twice within a single request. Need to cache.
     *
     * @return array
     * @throws \Magento\Webapi\Exception When GET parameters are invalid
     */
    public function getRequestedServices()
    {
        $wsdlParam = \Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_WSDL;
        $servicesParam = \Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_SERVICES;
        $requestParams = array_keys($this->getParams());
        $allowedParams = array($wsdlParam, $servicesParam);
        $notAllowedParameters = array_diff($requestParams, $allowedParams);
        if (count($notAllowedParameters)) {
            $notAllowed = implode(', ', $notAllowedParameters);
            $message =
                __('Not allowed parameters: %1. Please use only %2 and %3.', $notAllowed, $wsdlParam, $servicesParam);
            throw new \Magento\Webapi\Exception($message);
        }

        $param = $this->getParam($servicesParam);
        return $this->_convertRequestParamToServiceArray($param);
    }

    /**
     * Extract the resources query param value and return associative array of the form 'resource' => 'version'
     *
     * @param string $param eg <pre> testModule1AllSoapAndRest:V1,testModule2AllSoapNoRest:V1 </pre>
     * @return array <pre> eg array (
     *      'testModule1AllSoapAndRest' => 'V1',
     *       'testModule2AllSoapNoRest' => 'V1',
     *      )</pre>
     * @throws \Magento\Webapi\Exception
     */
    protected function _convertRequestParamToServiceArray($param)
    {
        $serviceSeparator = ',';
        //TODO: This should be a globally used pattern in Webapi module
        $serviceVerPattern = "[a-zA-Z\d]*V[\d]+";
        $regexp = "/^($serviceVerPattern)([$serviceSeparator]$serviceVerPattern)*$/";
        //Check if the $param is of valid format
        if (empty($param) || !preg_match($regexp, $param)) {
            $message = __('Incorrect format of WSDL request URI or Requested services are missing.');
            throw new \Magento\Webapi\Exception($message);
        }
        //Split the $param string to create an array of 'service' => 'version'
        $serviceVersionArray = explode($serviceSeparator, $param);
        $serviceArray = array();
        foreach ($serviceVersionArray as $service) {
            $serviceArray[] = $service;
        }
        return $serviceArray;
    }
}
