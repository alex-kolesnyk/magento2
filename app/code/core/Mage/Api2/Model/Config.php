<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Api2
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Webservice api2 config model
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Config extends Varien_Simplexml_Config //extends Mage_Api_Model_Config
{
    /**
     * Id for config cache
     */
    const CACHE_ID  = 'config_api2';

    /**
     * Tag name for config cache
     */
    const CACHE_TAG = 'config_api2';

    private $_typesForRequest = array(
        'text'                  => 'api2/request_interpreter_query',
        'text/plain'            => 'api2/request_interpreter_query',
        'text/html'             => 'api2/request_interpreter_html',
        'json'                  => 'api2/request_interpreter_json',
        'application/json'      => 'api2/request_interpreter_json',
        'xml'                   => 'api2/request_interpreter_xml',
        'application/xml'       => 'api2/request_interpreter_xml',
        'application/xhtml+xml' => 'api2/request_interpreter_xml',
        'text/xml'              => 'api2/request_interpreter_xml',
    );

    private $_typesForResponse = array(
        'text'                  => 'api2/renderer_query',
        'text/plain'            => 'api2/renderer_query',
        'text/html'             => 'api2/renderer_html',
        'json'                  => 'api2/renderer_json',
        'application/json'      => 'api2/renderer_json',
        'xml'                   => 'api2/renderer_xml',
        'application/xml'       => 'api2/renderer_xml',
        'application/xhtml+xml' => 'api2/renderer_xml',
        'text/xml'              => 'api2/renderer_xml',
    );

    /**
     * Constructor
     * Initializes XML for this configuration
     *
     * @param string|Varien_Simplexml_Element $sourceData
     */
    public function __construct($sourceData=null)
    {
        $this->setCacheId(self::CACHE_ID)->setCacheTags(array(self::CACHE_TAG));

        parent::__construct($sourceData);
        $this->_construct();
    }

    /**
     * Fetch all routes of the given api type from config files api2.xml
     *
     * @param string $apiType
     * @throws Mage_Api2_Exception
     * @return array
     */
    public function getRoutes($apiType)
    {
        if (Mage_Api2_Model_Server::API_TYPE_REST == $apiType) {
            $routes = $this->getRoutesRest();
        } elseif (Mage_Api2_Model_Server::API_TYPE_SOAP == $apiType) {
            $routes = $this->getRoutesSoap();
        } else {
            throw new Mage_Api2_Exception(sprintf('Invalid API type "%s".', $apiType),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
        return $routes;
    }

    /**
     * Get MIME type mapping to Request interpreter class
     *
     * @static
     * @return array
     */
    public function getMimeTypesMappingForRequest()
    {
        //TODO fetch this from config files
        return $this->_typesForRequest;
    }

    /**
     * Get MIME type mapping to render Response body
     *
     * @static
     * @return array
     */
    public function getMimeTypesMappingForResponse()
    {
        //TODO fetch this from config files
        return $this->_typesForResponse;
    }

    /**
     * Init configuration for WS API
     *
     * @return Mage_Api2_Model_Config
     */
    protected function _construct()
    {
        if (Mage::app()->useCache(self::CACHE_ID)) {
            if ($this->loadCache()) {
                return $this;
            }
        }

        $config = Mage::getConfig()->loadModulesConfiguration('api2.xml');
        $this->setXml($config->getNode('api2'));

        if (Mage::app()->useCache(self::CACHE_ID)) {
            $this->saveCache();
        }
        return $this;
    }

    /**
     * Retrieve all resources from config files api2.xml
     *
     * @return Varien_Simplexml_Element
     */
    protected function getResources()
    {
        return $this->getNode('resources')->children();
    }

    /**
     * Fetch all routes for REST API
     *
     * @return array
     */
    protected function getRoutesRest()
    {
        $routes = array();
        foreach ($this->getResources() as $resource) {
            if (!$resource->routes) {
                continue;
            }

            foreach ($resource->routes->children() as $route) {
                $mask = (string)$route->mask;
                $defaults = array(
                    'model' => (string)$resource->model,
                    'type'  => (string)$resource->type,
                );

                $reqs = array();
                $routes[] = new Mage_Api2_Model_Route_Rest($mask, $defaults, $reqs);
            }
        }
        return $routes;
    }

    /**
     * Fetch all routes for SOAP API
     *
     * @return array
     */
    protected function getRoutesSoap()
    {
        return array();
    }
}
