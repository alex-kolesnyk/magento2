<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */


class Mage_Backend_Controller_Router_Default extends Mage_Core_Controller_Varien_Router_Base
{
    /**
     * Default route id
     */
    const DEFAULT_ROUTE_ID = 'adminhtml';

    /**
     * List of required request parameters
     * Order sensitive
     * @var array
     */
    protected $_requiredParams = array(
        'areaFrontName',
        'moduleFrontName',
        'controllerName',
        'actionName',
    );

    /**
     * Url key of area
     *
     * @var string
     */
    protected $_areaFrontName;

    /**
     * @param Mage_Core_Controller_Varien_Action_Factory $controllerFactory
     * @param Magento_Filesystem $filesystem
     * @param Mage_Core_Model_App $app
     * @param Mage_Core_Model_Config_Scope $configScope
     * @param Mage_Core_Model_Router_Config $routerConfig
     * @param string $areaCode
     * @param string $baseController
     * @param string $routerId
     * @throws InvalidArgumentException
     */
    public function __construct(
        Mage_Core_Controller_Varien_Action_Factory $controllerFactory,
        Magento_Filesystem $filesystem,
        Mage_Core_Model_App $app,
        Mage_Core_Model_Config_Scope $configScope,
        Mage_Core_Model_Router_Config $routerConfig,
        $areaCode,
        $baseController,
        $routerId
    ) {
        parent::__construct($controllerFactory, $filesystem, $app, $configScope, $routerConfig, $areaCode,
            $baseController, $routerId);

        $this->_areaFrontName = Mage::helper('Mage_Backend_Helper_Data')->getAreaFrontName();
        if (empty($this->_areaFrontName)) {
            throw new InvalidArgumentException('Area Front Name should be defined');
        }
    }

    /**
     * Fetch default path
     */
    public function fetchDefault()
    {
        $moduleFrontName = $this->getDefaultModuleFrontName();
        // set defaults
        $pathParts = explode('/', $this->_getDefaultPath());
        $this->getFront()->setDefault(array(
            'area'       => $this->_getParamWithDefaultValue($pathParts, 0, ''),
            'module'     => $this->_getParamWithDefaultValue($pathParts, 1, $moduleFrontName),
            'controller' => $this->_getParamWithDefaultValue($pathParts, 2, 'index'),
            'action'     => $this->_getParamWithDefaultValue($pathParts, 3, 'index'),
        ));
    }

    /**
     * Get first backend route as a default
     *
     * @return string
     */
    public function getDefaultModuleFrontName()
    {
        $backendRoutes = $this->_getRoutes();
        $defaultRoute = $backendRoutes[self::DEFAULT_ROUTE_ID];
        return $defaultRoute['frontName'];
    }

    /**
     * Retrieve array param by key, or default value
     *
     * @param array $array
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    protected function _getParamWithDefaultValue($array, $key, $defaultValue)
    {
        return !empty($array[$key]) ? $array[$key] : $defaultValue;
    }

    /**
     * Get router default request path
     * @return string
     */
    protected function _getDefaultPath()
    {
        return (string)Mage::getConfig()->getNode('default/web/default/admin');
    }

    /**
     * Dummy call to pass through checking
     *
     * @return boolean
     */
    protected function _beforeModuleMatch()
    {
        return true;
    }

    /**
     * checking if we installed or not and doing redirect
     *
     * @return bool
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    protected function _afterModuleMatch()
    {
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }
        return true;
    }

    /**
     * We need to have noroute action in this router
     * not to pass dispatching to next routers
     *
     * @return bool
     */
    protected function _noRouteShouldBeApplied()
    {
        return true;
    }

    /**
     * Check whether URL for corresponding path should use https protocol
     *
     * @param string $path
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _shouldBeSecure($path)
    {
        return substr((string)Mage::getConfig()->getNode('default/web/unsecure/base_url'), 0, 5) === 'https'
            || Mage::getStoreConfigFlag('web/secure/use_in_adminhtml', Mage_Core_Model_AppInterface::ADMIN_STORE_ID)
                && substr((string)Mage::getConfig()->getNode('default/web/secure/base_url'), 0, 5) === 'https';
    }

    /**
     * Retrieve current secure url
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return string
     */
    protected function _getCurrentSecureUrl($request)
    {
        return Mage::app()->getStore(Mage_Core_Model_AppInterface::ADMIN_STORE_ID)
            ->getBaseUrl('link', true) . ltrim($request->getPathInfo(), '/');
    }

    /**
     * Check whether redirect should be used for secure routes
     *
     * @return bool
     */
    protected function _shouldRedirectToSecure()
    {
        return false;
    }

    /**
     * Build controller class name based on moduleName and controllerName
     *
     * @param string $realModule
     * @param string $controller
     * @return string
     */
    public function getControllerClassName($realModule, $controller)
    {
        /**
         * Start temporary block
         * TODO: Sprint#27. Delete after adminhtml refactoring
         */
        if ($realModule == 'Mage_Adminhtml') {
            return parent::getControllerClassName($realModule, $controller);
        }
        /**
         * End temporary block
         */

        $parts = explode('_', $realModule);
        $realModule = implode('_', array_splice($parts, 0, 2));
        return $realModule . '_' . 'Controller' . '_'. ucfirst($this->_areaCode) . '_' . uc_words($controller);
    }

    /**
     * Check whether this router should process given request
     *
     * @param array $params
     * @return bool
     */
    protected function _canProcess(array $params)
    {
        return $params['areaFrontName'] == $this->_areaFrontName;
    }
}
