<?php
/**
 * {license_notice}
 *
 * @category   Magento
 * @package    Magento_Core
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Main Mage hub class
 */
final class Mage
{
    /**
     * Default error handler function name
     */
    const DEFAULT_ERROR_HANDLER = 'mageCoreErrorHandler';

    /**
     * Application initialization option to specify custom product edition label
     */
    const PARAM_EDITION = 'edition';

    /**
     * Application run code
     */
    const PARAM_RUN_CODE = 'MAGE_RUN_CODE';

    /**
     * Application run type (store|website)
     */
    const PARAM_RUN_TYPE = 'MAGE_RUN_TYPE';

    /**
     * Application run code
     */
    const PARAM_MODE = 'MAGE_MODE';

    /**
     * Base directory
     */
    const PARAM_BASEDIR = 'base_dir';

    /**
     * Custom application dirs
     */
    const PARAM_APP_DIRS = 'app_dirs';

    /**
     * Custom application uris
     */
    const PARAM_APP_URIS = 'app_uris';

    /**
     * Allowed modules
     */
    const PARAM_ALLOWED_MODULES = 'allowed_modules';

    /**
     * Caching params
     */
    const PARAM_CACHE_OPTIONS = 'cache_options';

    /**
     * Disallow cache
     */
    const PARAM_BAN_CACHE = 'global_ban_use_cache';

    /**
     * Custom local configuration file name
     */
    const PARAM_CUSTOM_LOCAL_FILE = 'custom_local_xml';

    /**
     * Custom local configuration
     */
    const PARAM_CUSTOM_LOCAL_CONFIG = 'custom_local_config';

    /**#@+
     * Product edition labels
     */
    const EDITION_COMMUNITY    = 'Community';
    const EDITION_ENTERPRISE   = 'Enterprise';
    const EDITION_PROFESSIONAL = 'Professional';
    const EDITION_GO           = 'Go';
    /**#@-*/

    /**
     * Default timezone
     */
    const DEFAULT_TIMEZONE  = 'UTC';

    /**
     * Magento version
     */
    const VERSION = '2.0.0.0-dev43';

    /**
     * Application root absolute path
     *
     * @var string
     */
    static private $_appRoot;

    /**
     * Is downloader flag
     *
     * @var bool
     */
    static private $_isDownloader = false;

    /**
     * Is allow throw Exception about headers already sent
     *
     * @var bool
     */
    public static $headersSentThrowsException  = true;

    /**
     * Current Magento edition.
     *
     * @var string
     * @static
     */
    static private $_currentEdition = self::EDITION_COMMUNITY;

    /**
     * Check if we need to use __sleep and __wakeup serialization methods in models
     *
     * @var bool
     */
    static private $_isSerializable = true;

    /**
     * Update mode flag
     *
     * @var bool
     */
    static private $_updateMode = false;

    /**
     * Gets the current Magento version string
     * @link http://www.magentocommerce.com/blog/new-community-edition-release-process/
     *
     * @return string
     */
    public static function getVersion()
    {
        $info = self::getVersionInfo();
        return trim("{$info['major']}.{$info['minor']}.{$info['revision']}"
            . ($info['patch'] != '' ? ".{$info['patch']}" : "")
            . "-{$info['stability']}{$info['number']}", '.-');
    }

    /**
     * Gets the detailed Magento version information
     * @link http://www.magentocommerce.com/blog/new-community-edition-release-process/
     *
     * @return array
     */
    public static function getVersionInfo()
    {
        return array(
            'major'     => '2',
            'minor'     => '0',
            'revision'  => '0',
            'patch'     => '0',
            'stability' => 'dev',
            'number'    => '45',
        );
    }

    /**
     * Get current Magento edition
     *
     * @static
     * @return string
     */
    public static function getEdition()
    {
        return self::$_currentEdition;
    }

    /**
     * Set edition
     *
     * @param string $edition
     */
    public static function setEdition($edition)
    {
        self::$_currentEdition = $edition;
    }

    /**
     * Retrieve application root absolute path
     *
     * @return string
     * @throws Magento_Exception
     */
    public static function getRoot()
    {
        if (!self::$_appRoot) {
            $appRootDir = __DIR__;
            if (!is_readable($appRootDir)) {
                throw new Magento_Exception("Application root directory '$appRootDir' is not readable.");
            }
            self::$_appRoot = $appRootDir;
        }
        return self::$_appRoot;
    }

    /**
     * Retrieve application root absolute path
     *
     * @deprecated
     * @param string $type
     * @return string
     */
    public static function getBaseDir($type = Magento_Core_Model_Dir::ROOT)
    {
        return self::getSingleton('Magento_Core_Model_Dir')->getDir($type);
    }

    /**
     * Retrieve module absolute path by directory type
     *
     * @param string $type
     * @param string $moduleName
     * @return string
     */
    public static function getModuleDir($type, $moduleName)
    {
        return Magento_Core_Model_ObjectManager::getInstance()
            ->get('Magento_Core_Model_Config_Modules_Reader')
            ->getModuleDir($type, $moduleName);
    }

    /**
     * Get base URL path by type
     *
     * @deprecated
     * @param string $type
     * @param null|bool $secure
     * @return string
     */
    public static function getBaseUrl($type = Magento_Core_Model_Store::URL_TYPE_LINK, $secure = null)
    {
        return self::app()->getStore()->getBaseUrl($type, $secure);
    }

    /**
     * Generate url by route and parameters
     *
     * @deprecated
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public static function getUrl($route = '', $params = array())
    {
        return Magento_Core_Model_ObjectManager::getInstance()
            ->create('Magento_Core_Model_Url')
            ->getUrl($route, $params);
    }

    /**
     * Retrieve model object
     *
     * @deprecated
     * @param   string $modelClass
     * @param   array|object $arguments
     * @return  Magento_Core_Model_Abstract|false
     */
    public static function getModel($modelClass = '', $arguments = array())
    {
        if (!is_array($arguments)) {
            $arguments = array($arguments);
        }
        return Magento_Core_Model_ObjectManager::getInstance()->create($modelClass, $arguments);
    }

    /**
     * Retrieve model object singleton
     *
     * @deprecated
     * @param   string $modelClass
     * @return  Magento_Core_Model_Abstract
     */
    public static function getSingleton($modelClass = '')
    {
        $registryKey = '_singleton/' . $modelClass;
        $objectManager = Magento_Core_Model_ObjectManager::getInstance();
        /** @var Magento_Core_Model_Registry $registryObject */
        $registryObject = $objectManager->get('Magento_Core_Model_Registry');
        if (!$registryObject->registry($registryKey)) {
            $registryObject->register($registryKey, $objectManager->get($modelClass));
        }
        return $registryObject->registry($registryKey);
    }

    /**
     * Retrieve object of resource model
     *
     * @deprecated
     * @param   string $modelClass
     * @param   array $arguments
     * @return  Object
     */
    public static function getResourceModel($modelClass, $arguments = array())
    {
        if (!is_array($arguments)) {
            $arguments = array($arguments);
        }
        return Magento_Core_Model_ObjectManager::getInstance()->create($modelClass, $arguments);
    }

    /**
     * Retrieve resource model object singleton
     *
     * @deprecated
     * @param   string $modelClass
     * @return  object
     */
    public static function getResourceSingleton($modelClass = '')
    {
        $objectManager = Magento_Core_Model_ObjectManager::getInstance();
        /** @var Magento_Core_Model_Registry $registryObject */
        $registryObject = $objectManager->get('Magento_Core_Model_Registry');
        $registryKey = '_resource_singleton/' . $modelClass;
        if (!$registryObject->registry($registryKey)) {
            $registryObject->register($registryKey, $objectManager->get($modelClass));
        }
        return $registryObject->registry($registryKey);
    }

    /**
     * Returns block singleton instance, if current action exists. Otherwise returns FALSE.
     *
     * @deprecated
     * @param string $className
     * @return mixed
     */
    public static function getBlockSingleton($className)
    {
        $action = self::app()->getFrontController()->getAction();
        return $action ? $action->getLayout()->getBlockSingleton($className) : false;
    }

    /**
     * Retrieve resource helper object
     *
     * @deprecated
     * @param string $moduleName
     * @return Magento_Core_Model_Resource_Helper_Abstract
     */
    public static function getResourceHelper($moduleName)
    {
        return Magento_Core_Model_ObjectManager::getInstance()->get('Magento_Core_Model_Resource_HelperPool')
            ->get($moduleName);
    }

    /**
     * Return new exception by module to be thrown
     *
     * @deprecated
     * @param string $module
     * @param string $message
     * @param integer $code
     * @return Magento_Core_Exception
     */
    public static function exception($module = 'Magento_Core', $message = '', $code = 0)
    {
        $className = $module . '_Exception';
        return new $className($message, $code);
    }

    /**
     * Throw Exception
     *
     * @deprecated
     * @param string $message
     * @param string $messageStorage
     * @throws Magento_Core_Exception
     */
    public static function throwException($message, $messageStorage = null)
    {
        if ($messageStorage && ($storage = self::getSingleton($messageStorage))) {
            $storage->addError($message);
        }
        throw new Magento_Core_Exception($message);
    }

    /**
     * Get application object.
     *
     * @return Magento_Core_Model_App
     * @deprecated
     */
    public static function app()
    {
        return Magento_Core_Model_ObjectManager::getInstance()->get('Magento_Core_Model_App');
    }

    /**
     * Check if application is installed
     *
     * @return bool
     * @deprecated use Magento_Core_Model_App_State::isInstalled()
     */
    public static function isInstalled()
    {
        return (bool) Mage::getSingleton('Magento_Core_Model_Config_Primary')->getInstallDate();
    }

    /**
     * Retrieve enabled developer mode
     *
     * @return bool
     * @deprecated use Magento_Core_Model_App_State::getMode()
     */
    public static function getIsDeveloperMode()
    {
        $objectManager = Magento_Core_Model_ObjectManager::getInstance();
        if (!$objectManager) {
            return false;
        }

        $appState = $objectManager->get('Magento_Core_Model_App_State');
        if (!$appState) {
            return false;
        }

        $mode = $appState->getMode();
        return $mode == Magento_Core_Model_App_State::MODE_DEVELOPER;
    }

    /**
     * Display exception
     *
     * @param Exception $e
     * @param string $extra
     */
    public static function printException(Exception $e, $extra = '')
    {
        if (self::getIsDeveloperMode()) {
            print '<pre>';

            if (!empty($extra)) {
                print $extra . "\n\n";
            }

            print $e->getMessage() . "\n\n";
            print $e->getTraceAsString();
            print '</pre>';
        } else {

            $reportData = array(
                !empty($extra) ? $extra . "\n\n" : '' . $e->getMessage(),
                $e->getTraceAsString()
            );

            // retrieve server data
            if (isset($_SERVER)) {
                if (isset($_SERVER['REQUEST_URI'])) {
                    $reportData['url'] = $_SERVER['REQUEST_URI'];
                }
                if (isset($_SERVER['SCRIPT_NAME'])) {
                    $reportData['script_name'] = $_SERVER['SCRIPT_NAME'];
                }
            }

            // attempt to specify store as a skin
            try {
                $storeCode = self::app()->getStore()->getCode();
                $reportData['skin'] = $storeCode;
            } catch (Exception $e) {
            }

            require_once(self::getBaseDir(Magento_Core_Model_Dir::PUB) . DS . 'errors' . DS . 'report.php');
        }

        die();
    }

    /**
     * Set is downloader flag
     *
     * @param bool $flag
     *
     * @deprecated use Magento_Core_Model_App_State::setIsDownloader()
     */
    public static function setIsDownloader($flag = true)
    {
        self::$_isDownloader = $flag;
    }

    /**
     * Set is serializable flag
     *
     * @static
     * @param bool $value
     *
     * @deprecated use Magento_Core_Model_App_State::setIsSerializable()
     */
    public static function setIsSerializable($value = true)
    {
        self::$_isSerializable = !empty($value);
    }

    /**
     * Get is serializable flag
     *
     * @static
     * @return bool
     *
     * @deprecated use Magento_Core_Model_App_State::getIsSerializable()
     */
    public static function getIsSerializable()
    {
        return self::$_isSerializable;
    }

    /**
     * Set update mode flag
     *
     * @param bool $value
     *
     * @deprecated use Magento_Core_Model_App_State::setUpdateMode()
     */
    public static function setUpdateMode($value)
    {
        self::$_updateMode = $value;

    }

    /**
     * Get update mode flag
     * @return bool
     *
     * @deprecated use Magento_Core_Model_App_State::setUpdateMode()
     */
    public static function getUpdateMode()
    {
        return self::$_updateMode;
    }
}
