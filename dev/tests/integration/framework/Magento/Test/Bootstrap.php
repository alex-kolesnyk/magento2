<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Bootstrap for the integration testing environment
 */
class Magento_Test_Bootstrap
{
    /**
     * Predefined admin user credentials
     */
    const ADMIN_NAME = 'user';
    const ADMIN_PASSWORD = 'password1';

    /**
     * Predefined admin user role name
     */
    const ADMIN_ROLE_NAME = 'Administrators';

    /**
     * @var Magento_Test_Bootstrap_Settings
     */
    private $_settings;

    /**
     * @var string
     */
    private $_dbVendorName;

    /**
     * @var Magento_Test_Application
     */
    private $_application;

    /**
     * @var Magento_Test_Bootstrap_Environment
     */
    private $_envBootstrap;

    /**
     * @var Magento_Test_Bootstrap_DocBlock
     */
    private $_docBlockBootstrap;

    /**
     * @var Magento_Test_Bootstrap_Profiler
     */
    private $_profilerBootstrap;

    /**
     * @var Magento_Shell
     */
    private $_shell;

    /**
     * Temporary directory to be used to host the application installation sandbox
     *
     * @var string
     */
    private $_tmpDir;

    /**
     * Constructor
     *
     * @param Magento_Test_Bootstrap_Settings $settings
     * @param Magento_Test_Bootstrap_Environment $envBootstrap
     * @param Magento_Test_Bootstrap_DocBlock $docBlockBootstrap
     * @param Magento_Test_Bootstrap_Profiler $profilerBootstrap
     * @param Magento_Shell $shell
     * @param string $tmpDir
     */
    public function __construct(
        Magento_Test_Bootstrap_Settings $settings,
        Magento_Test_Bootstrap_Environment $envBootstrap,
        Magento_Test_Bootstrap_DocBlock $docBlockBootstrap,
        Magento_Test_Bootstrap_Profiler $profilerBootstrap,
        Magento_Shell $shell,
        $tmpDir
    ) {
        $this->_settings = $settings;
        $this->_envBootstrap = $envBootstrap;
        $this->_docBlockBootstrap = $docBlockBootstrap;
        $this->_profilerBootstrap = $profilerBootstrap;
        $this->_shell = $shell;
        $this->_tmpDir = $tmpDir;
        $this->_application = $this->_createApplication(
            array(
                $this->_settings->getAsConfigFile('TESTS_LOCAL_CONFIG_FILE'),
                $this->_settings->getAsConfigFile('TESTS_LOCAL_CONFIG_EXTRA_FILE'),
            ),
            $this->_settings->getAsMatchingPaths('TESTS_GLOBAL_CONFIG_FILES'),
            $this->_settings->getAsMatchingPaths('TESTS_MODULE_CONFIG_FILES'),
            $this->_settings->get('TESTS_MAGENTO_MODE')
        );
    }

    /**
     * Retrieve the application instance
     *
     * @return Magento_Test_Application
     */
    public function getApplication()
    {
        return $this->_application;
    }

    /**
     * Retrieve the database vendor name
     *
     * @return string
     */
    public function getDbVendorName()
    {
        return $this->_dbVendorName;
    }

    /**
     * Perform bootstrap actions required to completely setup the testing environment
     */
    public function runBootstrap()
    {
        $this->_envBootstrap->emulateHttpRequest($_SERVER);
        $this->_envBootstrap->emulateSession($_SESSION);

        $profilerOutputFile = $this->_settings->getAsFile('TESTS_PROFILER_FILE');
        if ($profilerOutputFile) {
            $this->_profilerBootstrap->registerFileProfiler($profilerOutputFile);
        }

        $profilerOutputFile = $this->_settings->getAsFile('TESTS_BAMBOO_PROFILER_FILE');
        $profilerMetricsFile = $this->_settings->getAsFile('TESTS_BAMBOO_PROFILER_METRICS_FILE');
        if ($profilerOutputFile && $profilerMetricsFile) {
            $this->_profilerBootstrap->registerBambooProfiler($profilerOutputFile, $profilerMetricsFile);
        }

        $memoryBootstrap = $this->_createMemoryBootstrap(
            $this->_settings->get('TESTS_MEM_USAGE_LIMIT', 0), $this->_settings->get('TESTS_MEM_LEAK_LIMIT', 0)
        );
        $memoryBootstrap->activateStatsDisplaying();
        $memoryBootstrap->activateLimitValidation();

        $this->_docBlockBootstrap->registerAnnotations($this->_application);

        if ($this->_settings->getAsBoolean('TESTS_CLEANUP')) {
            $this->_application->cleanup();
        }
        if ($this->_application->isInstalled()) {
            $this->_application->initialize();
        } else {
            $this->_application->install(self::ADMIN_NAME, self::ADMIN_PASSWORD, self::ADMIN_ROLE_NAME);
        }
    }

    /**
     * Create and return new memory bootstrap instance
     *
     * @param int $memUsageLimit
     * @param int $memLeakLimit
     * @return Magento_Test_Bootstrap_Memory
     */
    protected function _createMemoryBootstrap($memUsageLimit, $memLeakLimit)
    {
        return new Magento_Test_Bootstrap_Memory(new Magento_Test_MemoryLimit(
            $memUsageLimit, $memLeakLimit, new Magento_Test_Helper_Memory($this->_shell)
        ));
    }

    /**
     * Create and return new application instance
     *
     * @param array $localConfigFiles
     * @param array $globalConfigFiles
     * @param array $moduleConfigFiles
     * @param string $appMode
     * @return Magento_Test_Application
     */
    protected function _createApplication(
        array $localConfigFiles, array $globalConfigFiles, array $moduleConfigFiles, $appMode
    ) {
        $localConfigXml = $this->_loadConfigFiles($localConfigFiles);
        $dbConfig = $localConfigXml->global->resources->default_setup->connection;
        $this->_dbVendorName = $this->_determineDbVendorName($dbConfig);
        $sandboxUniqueId = $this->_calcConfigFilesHash($localConfigFiles);
        $installDir = "{$this->_tmpDir}/sandbox-{$this->_dbVendorName}-{$sandboxUniqueId}";
        $dbClass = 'Magento_Test_Db_' . ucfirst($this->_dbVendorName);
        /** @var $dbInstance Magento_Test_Db_DbAbstract */
        $dbInstance = new $dbClass(
            (string)$dbConfig->host,
            (string)$dbConfig->username,
            (string)$dbConfig->password,
            (string)$dbConfig->dbname,
            $this->_tmpDir,
            $this->_shell
        );
        return new Magento_Test_Application(
            $dbInstance, $installDir, $localConfigXml, $globalConfigFiles, $moduleConfigFiles, $appMode
        );
    }

    /**
     * Calculate and return hash of config files' contents
     *
     * @param array $configFiles
     * @return string
     */
    protected function _calcConfigFilesHash($configFiles)
    {
        $result = array();
        foreach ($configFiles as $configFile) {
            $result[] = sha1_file($configFile);
        }
        $result = md5(implode('_', $result));
        return $result;
    }

    /**
     * @param array $configFiles
     * @return Magento_Simplexml_Element
     */
    protected function _loadConfigFiles(array $configFiles)
    {
        /** @var $result Magento_Simplexml_Element */
        $result = simplexml_load_string('<config/>', 'Magento_Simplexml_Element');
        foreach ($configFiles as $configFile) {
            /** @var $configXml Magento_Simplexml_Element */
            $configXml = simplexml_load_file($configFile, 'Magento_Simplexml_Element');
            $result->extend($configXml);
        }
        return $result;
    }

    /**
     * Retrieve database vendor name from the database connection XML configuration
     *
     * @param SimpleXMLElement $dbConfig
     * @return string
     * @throws Magento_Exception
     */
    protected function _determineDbVendorName(SimpleXMLElement $dbConfig)
    {
        $dbVendorAlias = (string)$dbConfig->model;
        $dbVendorMap = array('mysql4' => 'mysql');
        if (!array_key_exists($dbVendorAlias, $dbVendorMap)) {
            throw new Magento_Exception("Database vendor '$dbVendorAlias' is not supported.");
        }
        return $dbVendorMap[$dbVendorAlias];
    }
}
