<?php
/**
 * Resources and connections registry and factory
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Magento_Core_Model_Resource
{
    const AUTO_UPDATE_CACHE_KEY  = 'DB_AUTOUPDATE';
    const AUTO_UPDATE_ONCE       = 0;
    const AUTO_UPDATE_NEVER      = -1;
    const AUTO_UPDATE_ALWAYS     = 1;

    const DEFAULT_READ_RESOURCE  = 'core_read';
    const DEFAULT_WRITE_RESOURCE = 'core_write';
    const DEFAULT_SETUP_RESOURCE = 'core_setup';

    /**
     * Instances of classes for connection types
     *
     * @var array
     */
    protected $_connectionTypes    = array();

    /**
     * Instances of actual connections
     *
     * @var array
     */
    protected $_connections        = array();

    /**
     * Names of actual connections that wait to set cache
     *
     * @var array
     */
    protected $_skippedConnections = array();

    /**
     * Registry of resource entities
     *
     * @var array
     */
    protected $_entities           = array();

    /**
     * Mapped tables cache array
     *
     * @var array
     */
    protected $_mappedTableNames;

    /**
     * Resource configuration
     *
     * @var Magento_Core_Model_Config_Resource
     */
    protected $_resourceConfig;

    /**
     * Application cache
     *
     * @var Magento_Core_Model_CacheInterface
     */
    protected $_cache;

    /**
     * Dirs instance
     *
     * @var Magento_Core_Model_Dir
     */
    protected $_dirs;

    /**
     * @param Magento_Core_Model_Config_Resource $resourceConfig
     * @param Magento_Core_Model_CacheInterface $cache
     * @param Magento_Core_Model_Dir $dirs
     */
    public function __construct(
        Magento_Core_Model_Config_Resource $resourceConfig,
        Magento_Core_Model_CacheInterface $cache,
        Magento_Core_Model_Dir $dirs
    ) {
        $this->_resourceConfig = $resourceConfig;
        $this->_cache = $cache;
        $this->_dirs = $dirs;
    }

    /**
     * Set resource configuration
     *
     * @param Magento_Core_Model_Config_Resource $resourceConfig
     */
    public function setResourceConfig(Magento_Core_Model_Config_Resource $resourceConfig)
    {
        $this->_resourceConfig = $resourceConfig;
    }

    /**
     * Set cache instance
     *
     * @param Magento_Core_Model_CacheInterface $cache
     */
    public function setCache(Magento_Core_Model_CacheInterface $cache)
    {
        $this->_cache = $cache;
    }

    /**
     * Creates a connection to resource whenever needed
     *
     * @param string $name
     * @return Magento_DB_Adapter_Interface
     */
    public function getConnection($name)
    {
        if (isset($this->_connections[$name])) {
            $connection = $this->_connections[$name];
            if (isset($this->_skippedConnections[$name])) {
                $connection->setCacheAdapter(Mage::app()->getCache());
                unset($this->_skippedConnections[$name]);
            }
            return $connection;
        }
        $connConfig = $this->_resourceConfig->getResourceConnectionConfig($name);

        if (!$connConfig) {
            $this->_connections[$name] = $this->_getDefaultConnection($name);
            return $this->_connections[$name];
        }
        if (!$connConfig->is('active', 1)) {
            return false;
        }

        $origName = $connConfig->getParent()->getName();
        if (isset($this->_connections[$origName])) {
            $this->_connections[$name] = $this->_connections[$origName];
            return $this->_connections[$origName];
        }

        $connection = $this->_newConnection((string)$connConfig->type, $connConfig);
        if ($connection) {
            $connection->setCacheAdapter($this->_cache->getFrontend());
        }

        $this->_connections[$name] = $connection;
        if ($origName !== $name) {
            $this->_connections[$origName] = $connection;
        }

        return $connection;
    }

    /**
     * Retrieve connection adapter class name by connection type
     *
     * @param string $type  the connection type
     * @return string|false
     */
    protected function _getConnectionAdapterClassName($type)
    {
        $config = $this->_resourceConfig->getResourceTypeConfig($type);
        if (!empty($config->adapter)) {
            return (string)$config->adapter;
        }
        return false;
    }

    /**
     * Create new connection adapter instance by connection type and config
     *
     * @param string $type the connection type
     * @param Magento_Core_Model_Config_Element|array $config the connection configuration
     * @return Magento_DB_Adapter_Interface|false
     */
    protected function _newConnection($type, $config)
    {
        if ($config instanceof Magento_Core_Model_Config_Element) {
            $config = $config->asArray();
        }
        if (!is_array($config)) {
            return false;
        }

        $connection = false;
        // try to get adapter and create connection
        $className  = $this->_getConnectionAdapterClassName($type);
        if ($className) {
            $connection = new $className($this->_dirs, $config);
            if ($connection instanceof Magento_DB_Adapter_Interface) {
                /** @var Zend_Db_Adapter_Abstract $connection */

                // Set additional params for Magento profiling tool
                $profiler = $connection->getProfiler();
                if ($profiler instanceof Magento_DB_Profiler) {
                    /** @var Magento_DB_Profiler $profiler */
                    $profiler->setType($type);

                    $host = !empty($config['host']) ? $config['host'] : '';
                    $profiler->setHost($host);
                }

                // run after initialization statements
                if (!empty($config['initStatements'])) {
                    $connection->query($config['initStatements']);
                }
            } else {
                $connection = false;
            }
        }

        // try to get connection from type
        if (!$connection) {
            $typeInstance = $this->getConnectionTypeInstance($type);
            /** @var Magento_Core_Model_Resource_Type_Abstract $typeInstance */
            $connection = $typeInstance->getConnection($config);
            if (!$connection instanceof Magento_DB_Adapter_Interface) {
                $connection = false;
            }
        }

        return $connection;
    }

    /**
     * Retrieve default connection name by required connection name
     *
     * @param string $requiredConnectionName
     * @return string
     */
    protected function _getDefaultConnection($requiredConnectionName)
    {
        if (strpos($requiredConnectionName, 'read') !== false) {
            return $this->getConnection(self::DEFAULT_READ_RESOURCE);
        }
        return $this->getConnection(self::DEFAULT_WRITE_RESOURCE);
    }

    /**
     * Get connection type instance
     *
     * Creates new if doesn't exist
     *
     * @param string $type
     * @return Magento_Core_Model_Resource_Type_Abstract
     */
    public function getConnectionTypeInstance($type)
    {
        if (!isset($this->_connectionTypes[$type])) {
            $config = $this->_resourceConfig->getResourceTypeConfig($type);
            $typeClass = $config->getClassName();
            $this->_connectionTypes[$type] = new $typeClass($this->_dirs);
        }
        return $this->_connectionTypes[$type];
    }

    /**
     * Get resource table name, validated by db adapter
     *
     * @param   string|array $modelEntity
     * @return  string
     */
    public function getTableName($modelEntity)
    {
        $tableSuffix = null;
        if (is_array($modelEntity)) {
            list($modelEntity, $tableSuffix) = $modelEntity;
        }

        $tableName = $modelEntity;

        $mappedTableName = $this->getMappedTableName($tableName);
        if ($mappedTableName) {
            $tableName = $mappedTableName;
        } else {
            $tablePrefix = (string)$this->_resourceConfig->getTablePrefix();
            if ($tablePrefix && strpos($tableName, $tablePrefix) !== 0) {
                $tableName = $tablePrefix . $tableName;
            }
        }

        if ($tableSuffix) {
            $tableName .= '_' . $tableSuffix;
        }
        return $this->getConnection(self::DEFAULT_READ_RESOURCE)->getTableName($tableName);
    }

    /**
     * Set mapped table name
     *
     * @param string $tableName
     * @param string $mappedName
     * @return Magento_Core_Model_Resource
     */
    public function setMappedTableName($tableName, $mappedName)
    {
        $this->_mappedTableNames[$tableName] = $mappedName;
        return $this;
    }

    /**
     * Get mapped table name
     *
     * @param string $tableName
     * @return bool|string
     */
    public function getMappedTableName($tableName)
    {
        if (isset($this->_mappedTableNames[$tableName])) {
            return $this->_mappedTableNames[$tableName];
        } else {
            return false;
        }
    }

    /**
     * Create new connection with custom config
     *
     * @param string $name
     * @param string $type
     * @param array $config
     * @return unknown
     */
    public function createConnection($name, $type, $config)
    {
        if (!isset($this->_connections[$name])) {
            $connection = $this->_newConnection($type, $config);

            $this->_connections[$name] = $connection;
        }
        return $this->_connections[$name];
    }

    public function checkDbConnection()
    {
        if (!$this->getConnection('core_read')) {
            //Mage::app()->getResponse()->setRedirect(Mage::getUrl('install'));
        }
    }

    public function getAutoUpdate()
    {
        return self::AUTO_UPDATE_ALWAYS;
        #return Mage::app()->loadCache(self::AUTO_UPDATE_CACHE_KEY);
    }

    public function setAutoUpdate($value)
    {
        #Mage::app()->saveCache($value, self::AUTO_UPDATE_CACHE_KEY);
        return $this;
    }
    /**
     * Retrieve 32bit UNIQUE HASH for a Table index
     *
     * @param string $tableName
     * @param array|string $fields
     * @param string $indexType
     * @return string
     */
    public function getIdxName($tableName, $fields, $indexType = Magento_DB_Adapter_Interface::INDEX_TYPE_INDEX)
    {
        return $this->getConnection(self::DEFAULT_READ_RESOURCE)
            ->getIndexName($this->getTableName($tableName), $fields, $indexType);
    }

    /**
     * Retrieve 32bit UNIQUE HASH for a Table foreign key
     *
     * @param string $priTableName  the target table name
     * @param string $priColumnName the target table column name
     * @param string $refTableName  the reference table name
     * @param string $refColumnName the reference table column name
     * @return string
     */
    public function getFkName($priTableName, $priColumnName, $refTableName, $refColumnName)
    {
        return $this->getConnection(self::DEFAULT_READ_RESOURCE)
            ->getForeignKeyName($this->getTableName($priTableName), $priColumnName,
                $this->getTableName($refTableName), $refColumnName);
    }
}
