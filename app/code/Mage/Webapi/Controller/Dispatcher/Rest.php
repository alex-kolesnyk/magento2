<?php
/**
 * Dispatcher for REST API calls.
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Webapi_Controller_Dispatcher_Rest implements Mage_Webapi_Controller_DispatcherInterface
{
    /** @var Mage_Webapi_Controller_Dispatcher_Rest_Presentation */
    protected $_restPresentation;

    /** @var Mage_Webapi_Controller_Router_Rest */
    protected $_router;

    /** @var Mage_Webapi_Controller_Dispatcher_Rest_Authentication */
    protected $_authentication;

    /** @var Mage_Webapi_Controller_Request_Rest */
    protected $_request;

    /** @var Mage_Webapi_Model_Authorization */
    protected $_authorization;

    /** @var Mage_Webapi_Controller_Response_Rest */
    protected $_response;

    /** @var Mage_Core_Service_Factory */
    protected $_serviceFactory;

    /**
     * Initialize dependencies.
     *
     * @param Mage_Webapi_Controller_Request_Rest $request
     * @param Mage_Webapi_Controller_Response_Rest $response
     * @param Mage_Webapi_Controller_Dispatcher_Rest_Presentation $restPresentation
     * @param Mage_Webapi_Controller_Router_Rest $router
     * @param Mage_Webapi_Controller_Dispatcher_Rest_Authentication $authentication
     * @param Mage_Core_Service_Factory $serviceFactory
     */
    public function __construct(
        Mage_Webapi_Controller_Request_Rest $request,
        Mage_Webapi_Controller_Response_Rest $response,
        Mage_Webapi_Controller_Dispatcher_Rest_Presentation $restPresentation,
        Mage_Webapi_Controller_Router_Rest $router,
        // TODO: Mage_Webapi_Model_Authorization $authorization,
        Mage_Webapi_Controller_Dispatcher_Rest_Authentication $authentication,
        Mage_Core_Service_Factory $serviceFactory
    ) {
        $this->_restPresentation = $restPresentation;
        $this->_router = $router;
        $this->_authentication = $authentication;
        // TODO: $this->_authorization = $authorization;
        $this->_request = $request;
        $this->_response = $response;
        $this->_serviceFactory = $serviceFactory;
    }

    /**
     * Handle REST request.
     *
     * @return Mage_Webapi_Controller_Dispatcher_Rest
     */
    public function dispatch()
    {
        try {
            // TODO: $this->_authentication->authenticate();
            $route = $this->_router->match($this->_request);
            $inputData = $this->_restPresentation->fetchRequestData();
            // TODO: $this->_authorization->checkResourceAcl($route->getServiceId(), $route->getServiceMethod());
            $serviceMethod = $route->getServiceMethod();
            $service = $this->_serviceFactory->createServiceInstance(
                $route->getServiceId(),
                $serviceMethod,
                $route->getServiceVersion()
            );
            $outputData = $service->$serviceMethod($inputData);
            if ($outputData instanceof Varien_Object || $outputData instanceof Varien_Data_Collection_Db) {
                $outputData = $outputData->getData();
            }
            $this->_restPresentation->prepareResponse($outputData);
        } catch (Exception $e) {
            $this->_response->setException($e);
        }
        $this->_response->sendResponse();
        return $this;
    }
}
