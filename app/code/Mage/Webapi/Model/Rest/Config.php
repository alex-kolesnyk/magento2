<?php
/**
 * Webapi Config Model for Rest.
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Webapi_Model_Rest_Config
{
    /**#@+
     * HTTP methods supported by REST.
     */
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_PUT = 'PUT';
    const HTTP_METHOD_POST = 'POST';
    /**#@-*/

    /** @var Mage_Webapi_Model_Config  */
    protected $_config;

    /** @var Magento_Controller_Router_Route_Factory */
    protected $_routeFactory;

    /**
     * @param Mage_Webapi_Model_Config
     * @param Magento_Controller_Router_Route_Factory $routeFactory
     */
    public function __construct(
        Mage_Webapi_Model_Config $config,
        Magento_Controller_Router_Route_Factory $routeFactory
    ) {
        $this->_config = $config;
        $this->_routeFactory = $routeFactory;
    }

    /**
     * Create route object.
     *
     * @param array $routeData Expected format:
     *  <pre>array(
     *      'routePath' => '/categories/:categoryId',
     *      'httpMethod' => 'GET',
     *      'version' => 1,
     *      'serviceId' => 'Mage_Catalog_Service_CategoryService',
     *      'serviceMethod' => 'item'
     *      'secure' => true
     *  );</pre>
     * @return Mage_Webapi_Controller_Rest_Router_Route
     */
    protected function _createRoute($routeData)
    {
        /** @var $route Mage_Webapi_Controller_Rest_Router_Route */
        $route = $this->_routeFactory->createRoute(
            'Mage_Webapi_Controller_Rest_Router_Route',
            strtolower($routeData['routePath'])
        );

        $route->setServiceId($routeData['serviceId'])
            ->setHttpMethod($routeData['httpMethod'])
            ->setServiceMethod($routeData['serviceMethod'])
            ->setServiceVersion(Mage_Webapi_Model_Config::VERSION_NUMBER_PREFIX . $routeData['version'])
            ->setSecure($routeData[Mage_Webapi_Model_Config::SECURE_ATTR_NAME]);
        return $route;
    }

    /**
     * Get service base URL
     *
     * @param Mage_Webapi_Controller_Rest_Request $request
     * @return string|null
     */
    protected function _getServiceBaseUrl($request)
    {
        $baseUrlRegExp = '#^/?\w+/\w+#';
        $serviceBaseUrl = preg_match($baseUrlRegExp, $request->getPathInfo(), $matches) ? $matches[0] : null;

        return $serviceBaseUrl;
    }

    /**
     * Generate the list of available REST routes.
     *
     * @param Mage_Webapi_Controller_Rest_Request $request
     * @return array
     * @throws Mage_Webapi_Exception
     */
    public function getRestRoutes(Mage_Webapi_Controller_Rest_Request $request)
    {
        $serviceBaseUrl = $this->_getServiceBaseUrl($request);
        $httpMethod = $request->getHttpMethod();
        $routes = array();
        foreach ($this->_config->getServices() as $serviceName => $serviceData) {
            // skip if baseurl is not null and does not match
            if (
                !isset($serviceData['baseUrl'])
                || !$serviceBaseUrl
                || strcasecmp(trim($serviceBaseUrl, '/'), trim($serviceData['baseUrl'], '/')) !== 0
            ) {
                // baseurl does not match, just skip this service
                continue;
            }
            // TODO: skip if version is not null and does not match
            foreach ($serviceData[Mage_Webapi_Model_Config::KEY_OPERATIONS] as $operationName => $operationData) {
                if (strtoupper($operationData['httpMethod']) == strtoupper($httpMethod)) {
                    $secure = isset($operationData[Mage_Webapi_Model_Config::SECURE_ATTR_NAME])
                        ? $operationData[Mage_Webapi_Model_Config::SECURE_ATTR_NAME]
                        : false;
                    $methodRoute = isset($operationData['route']) ? $operationData['route'] : '';
                    $routes[] = $this->_createRoute(
                        array(
                            'routePath' => $serviceData['baseUrl'] . $methodRoute,
                            'version' => $request->getServiceVersion(), // TODO: Take version from config
                            'serviceId' => $serviceName,
                            'serviceMethod' => $operationName,
                            'httpMethod' => $httpMethod,
                            Mage_Webapi_Model_Config::SECURE_ATTR_NAME => $secure
                        )
                    );
                }
            }
        }

        return $routes;
    }
}
