<?php

/**
 * Web API configuration.
 *
 * This class store information about web api. Most of it is needed by REST
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Webapi_Config
{
    const CACHE_ID = 'webapi';
    const KEY_OPERATIONS = 'operations';

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * @var Mage_Core_Model_Cache_Type_Config
     */
    protected $_configCacheType;

    /**
     * @var Mage_Webapi_Config_Reader
     */
    protected $_reader;

    /**
     * Module configuration reader
     *
     * @var Mage_Core_Model_Config_Modules_Reader
     */
    protected $_moduleReader;

    /**
     * @var Varien_Object
     */
    protected $_services = null;

    /** @var Magento_Controller_Router_Route_Factory */
    protected $_routeFactory;

    /**
     * List of SOAP operations available in the system
     *
     * @var array
     */
    protected $_soapOperations;

    /** @var Mage_Webapi_Helper_Config */
    protected $_helper;

    /**
     * @param Mage_Core_Model_Config $config
     * @param Mage_Core_Model_Cache_Type_Config $configCacheType
     * @param Mage_Core_Model_Config_Modules_Reader $moduleReader
     * @param Magento_Controller_Router_Route_Factory $routeFactory
     * @param Mage_Webapi_Helper_Config $helper
     */
    public function __construct(
        Mage_Core_Model_Config $config,
        Mage_Core_Model_Cache_Type_Config $configCacheType,
        Mage_Core_Model_Config_Modules_Reader $moduleReader,
        Magento_Controller_Router_Route_Factory $routeFactory,
        Mage_Webapi_Helper_Config $helper
    ) {
        $this->_config = $config;
        $this->_configCacheType = $configCacheType;
        $this->_moduleReader = $moduleReader;
        $this->_routeFactory = $routeFactory;
        $this->_helper = $helper;
    }

    /**
     * Retrieve list of service files from each module
     *
     * @return array
     */
    protected function _getConfigFile()
    {
        $files = $this->_moduleReader->getModuleConfigurationFiles('webapi.xml');
        return (array)$files;
    }

    /**
     * Reader object initialization
     *
     * @return Mage_Webapi_Config_Reader
     */
    protected function _getReader()
    {
        if (null === $this->_reader) {
            $configFiles = $this->_getConfigFile();
            $this->_reader = $this->_config->getModelInstance(
                'Mage_Webapi_Config_Reader',
                array('configFiles' => $configFiles)
            );
        }
        return $this->_reader;
    }


    /**
     * Return services loaded from cache if enabled or from files merged previously
     *
     * @return array
     */
    public function getServices()
    {
        if (null === $this->_services) {
            $services = $this->_loadFromCache();
            if ($services && is_string($services)) {
                $data = unserialize($services);
                $_array = isset($data['config']) ? $data['config'] : array();
                $this->_services = $_array; //new Varien_Object($_array);
            } else {
                $services = $this->_getReader()->getServices();
                $data = $this->_toArray($services);

                $this->_saveToCache(serialize($data));
                $_array = isset($data['config']) ? $data['config'] : array();
                $this->_services = $_array; //new Varien_Object($_array);\
            }
        }
        return $this->_services;
    }

    /**
     * Load services from cache
     */
    private function _loadFromCache()
    {
        return $this->_configCacheType->load(self::CACHE_ID);
    }

    /**
     * Save services into the cache
     * @param string $data serialized version of the webapi registry
     */
    protected function _saveToCache($data)
    {
        $this->_configCacheType->save($data, self::CACHE_ID);
        return $this;
    }

    /**
     * @param DOMDocument|DOMElement $root
     * @return array
     */
    protected function _toArray($root)
    {
        $result = array();

        if ($root->hasAttributes()) {
            foreach ($root->attributes as $attr) {
                $result[$attr->name] = $attr->value;
            }
        }

        $children = $root->childNodes;
        if ($children) {
            if ($children->length == 1) {
                $child = $children->item(0);
                if ($child->nodeType == XML_TEXT_NODE) {
                    $result['value'] = $child->nodeValue;
                    if (count($result) == 1) {
                        return $result['value'];
                    } else {
                        return $result;
                    }
                }
            }

            $group = array();

            for ($i = 0; $i < $children->length; $i++) {
                $child = $children->item($i);
                $_children = & $this->_toArray($child);

                $nodeId = isset($_children['class'])
                    ? $_children['class']
                    :
                    (isset($_children['method']) ? $_children['method'] : $child->nodeName);

                if ('rest-route' === $child->nodeName) {
                    if (!isset($result[self::KEY_OPERATIONS])) {
                        $result[self::KEY_OPERATIONS] = array();
                    }
                    $nodeId = isset($_children['method']) ? $_children['method'] : $child->nodeName;
                    if (!isset($result[self::KEY_OPERATIONS][$nodeId])) {
                        $result[self::KEY_OPERATIONS][$nodeId] = $_children;
                    } else {
                        $result[self::KEY_OPERATIONS][$nodeId] = array_merge(
                            $result['operations'][$nodeId],
                            $_children
                        );
                    }

                    $result[self::KEY_OPERATIONS][$nodeId]['route'] = $result[self::KEY_OPERATIONS][$nodeId]['value'];
                    unset($result[self::KEY_OPERATIONS][$nodeId]['value']);
                } else {
                    if (!isset($result[$nodeId])) {
                        $result[$nodeId] = $_children;
                    } else {
                        if (!isset($group[$nodeId])) {
                            $tmp = $result[$nodeId];
                            $result[$nodeId] = array($tmp);
                            $group[$nodeId] = 1;
                        }
                        $result[$nodeId][] = $_children;
                    }
                }
            }
        }
        unset($result['#text']);

        return $result;
    }

    /**
     * Retrieve info about the given service
     *
     * @param $serviceName Name
     * @param $serviceVersion Version
     * @throw InvalidArgumentException if the service does not exist
     */
    public function getService($serviceName)
    {
        if (isset($this->_services[$serviceName])) {
            return $this->_services[$serviceName];
        }

        throw new InvalidArgumentException("Service $serviceName does not exists");
    }

    /**
     * Add a new service (and/or method into the registry)
     *
     * @param string $serviceName used as SOAP service (e.g. product)
     * @param string $serviceClass Service class containing the method
     * @param string $baseUrl the base url for all route of thsi service (e.g. products)
     * @throw InvalidArgumentException if the service already exists
     */
    public function addService($serviceName, $serviceClass, $baseUrl)
    {
        if (isset($this->_services[$serviceName])) {
            throw new InvalidArgumentException("Service $serviceName already exists");
        } else {
            $this->_services[$serviceName] = array(
                'name' => $serviceName,
                'class' => $serviceClass,
                'baseUrl' => $baseUrl
            );
        }
    }

    /**
     * Retrieve info about the given operation
     *
     * @param string $serviceName
     * @param string $operation
     * @throw InvalidArgumentException if the service or operation do not exist
     */
    public function getOperation($serviceName, $operation)
    {
        $service = $this->getService($serviceName);

        if (isset($service[self::KEY_OPERATIONS][$operation])) {
            return $service[self::KEY_OPERATIONS][$operation];
        }

        throw new InvalidArgumentException("Operation $operation does not exist");
    }

    /**
     * Add a new route to an existing service
     *
     * @param string $serviceName e.g. product
     * @param string $operation name of the operation (must match the PHP method name) to add
     * @param string $httpMethod e.g. GET, POST
     * @param string $restRoute the route expression
     * @throw InvalidArgumentException if the service does not exist or the operation is already present
     */
    public function addOperation($serviceName, $operation, $httpMethod, $restRoute)
    {
        if (!isset($this->_services[$serviceName])) {
            throw new InvalidArgumentException("Service $serviceName does not exist");
        } elseif (isset($this->_services[$serviceName][self::KEY_OPERATIONS][$operation])) {
            throw new InvalidArgumentException("operation $operation already exists");
        } else {
            $this->_services[$serviceName][self::KEY_OPERATIONS][$operation] = array(
                'name' => $operation,
                'httpMethod' => $httpMethod,
                'route' => $restRoute
            );
        }
    }

    /**
     * @param Mage_Webapi_Controller_Request_Rest $request
     * @return array
     * @throws Mage_Webapi_Exception
     */
    public function getRestRoutes(Mage_Webapi_Controller_Request_Rest $request)
    {
        $baseUrlRegExp = '/^\/\w+/';
        preg_match($baseUrlRegExp, $request->getPathInfo(), $matches);
        $serviceBaseUrl = isset($matches[0]) ? $matches[0] : null;
        $httpMethod = $request->getHttpMethod();

        $routes = array();
        foreach ($this->getServices() as $serviceName => $serviceData) {
            // skip if baseurl is not null and does not match
            if ($serviceBaseUrl != null && strtolower($serviceBaseUrl) != strtolower($serviceData['baseUrl'])) {
                // baseurl does not match, just skip this service
                continue;
            }
            // TODO: skip if version is not null and does not match
            foreach ($serviceData[self::KEY_OPERATIONS] as $operationName => $operationData) {
                if (strtoupper($operationData['httpMethod']) == strtoupper($httpMethod)) {
                    $routes[] = $this->_createRoute(
                        array(
                            'routePath' => $serviceData['baseUrl'] . $operationData['route'],
                            'version' => $request->getResourceVersion(), // TODO: Take version from config
                            'serviceId' => $serviceName,
                            'serviceMethod' => $operationName,
                            'httpMethod' => $httpMethod
                        )
                    );
                }
            }
        }

        return $routes;
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
     *  );</pre>
     * @return Mage_Webapi_Controller_Router_Route_Rest
     */
    protected function _createRoute($routeData)
    {
        /** @var $route Mage_Webapi_Controller_Router_Route_Rest */
        $route = $this->_routeFactory->createRoute('Mage_Webapi_Controller_Router_Route_Rest', $routeData['routePath']);
        $route->setServiceId($routeData['serviceId'])
            ->setHttpMethod($routeData['httpMethod'])
            ->setServiceMethod($routeData['serviceMethod'])
            ->setServiceVersion(Mage_Core_Service_Config::VERSION_NUMBER_PREFIX . $routeData['version']);
        return $route;
    }

    /**
     * Retrieve the list of SOAP operations available in the system
     *
     * @return array <pre>
     * array(
     *     array(
     *         'class' => $serviceClass,
     *         'method' => $serviceMethod
     *     ),
     *      ...
     * )</pre>
     */
    protected function _getSoapOperations()
    {
        if (null == $this->_soapOperations) {
            $this->_soapOperations = array();
            $services = $this->getServices();
            foreach ($services as $serviceData) {
                $resourceName = $this->_helper->translateResourceName($serviceData['class']);
                foreach ($serviceData['operations'] as $method => $methodData) {
                    $operationName = $resourceName . ucfirst($method);
                    $this->_soapOperations[$operationName] = array(
                        'class' => $serviceData['class'],
                        'method' => $method
                    );
                }
            }
        }
        return $this->_soapOperations;
    }

    /**
     * Retrieve service class name corresponding to provided SOAP operation name.
     *
     * @param string $soapOperation
     * @return string
     * @throws Mage_Webapi_Exception
     */
    public function getClassBySoapOperation($soapOperation)
    {
        $soapOperations = $this->_getSoapOperations();
        if (!isset($soapOperations[$soapOperation])) {
            throw new Mage_Webapi_Exception(
                $this->_helper->__(
                    'Operation "%s" not found.',
                    $soapOperation
                ),
                Mage_Webapi_Exception::HTTP_NOT_FOUND
            );
        }
        return $soapOperations[$soapOperation]['class'];
    }

    /**
     * Retrieve service method name corresponding to provided SOAP operation name.
     *
     * @param string $soapOperation
     * @return string
     * @throws Mage_Webapi_Exception
     */
    public function getMethodBySoapOperation($soapOperation)
    {
        $soapOperations = $this->_getSoapOperations();
        if (!isset($soapOperations[$soapOperation])) {
            throw new Mage_Webapi_Exception(
                $this->_helper->__(
                    'Operation "%s" not found.',
                    $soapOperation
                ),
                Mage_Webapi_Exception::HTTP_NOT_FOUND
            );
        }
        return $soapOperations[$soapOperation]['method'];
    }
}
