<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Resource Setup Model
 */
class Magento_Core_Model_Resource_Setup implements Magento_Core_Model_Resource_SetupInterface
{
    /**
     * Setup resource name
     * @var string
     */
    protected $_resourceName;

    /**
     * Setup resource configuration object
     *
     * @var Magento_Simplexml_Element
     */
    protected $_resourceConfig;

    /**
     * Connection configuration object
     *
     * @var Magento_Simplexml_Element
     */
    protected $_connectionConfig;

    /**
     * Setup module configuration object
     *
     * @var Magento_Simplexml_Element
     */
    protected $_moduleConfig;

    /**
     * Call afterApplyAllUpdates method flag
     *
     * @var boolean
     */
    protected $_callAfterApplyAllUpdates = false;

    /**
     * Setup Connection
     *
     * @var Magento_DB_Adapter_Pdo_Mysql
     */
    protected $_conn;
    /**
     * Tables cache array
     *
     * @var array
     */
    protected $_tables = array();
    /**
     * Tables data cache array
     *
     * @var array
     */
    protected $_setupCache = array();

    /**
     * Flag which shows, that setup has hooked queries from DB adapter
     *
     * @var bool
     */
    protected $_queriesHooked = false;

    /**
     * Modules configuration
     *
     * @var Magento_Core_Model_Resource
     */
    protected $_resourceModel;

    /**
     * Modules configuration reader
     *
     * @var Magento_Core_Model_Config_Modules_Reader
     */
    protected $_modulesReader;

    /**
     * @var Magento_Core_Model_Config
     */
    protected $_config;

    /**
     * @param Magento_Core_Model_Config_Resource $resourcesConfig
     * @param Magento_Core_Model_Config $config
     * @param Magento_Core_Model_ModuleListInterface $moduleList
     * @param Magento_Core_Model_Resource $resource
     * @param Magento_Core_Model_Config_Modules_Reader $modulesReader
     * @param string $resourceName
     */
    public function __construct(
        Magento_Core_Model_Config_Resource $resourcesConfig,
        Magento_Core_Model_Config $config,
        Magento_Core_Model_ModuleListInterface $moduleList,
        Magento_Core_Model_Resource $resource,
        Magento_Core_Model_Config_Modules_Reader $modulesReader,
        $resourceName
    ) {
        $this->_config = $config;
        $resourcesConfig->setConfig($config);
        $this->_resourceModel = $resource;
        $this->_resourceName = $resourceName;
        $this->_modulesReader = $modulesReader;
        $this->_resourceConfig = $resourcesConfig->getResourceConfig($resourceName);
        $connection = $resourcesConfig->getResourceConnectionConfig($resourceName);
        if ($connection) {
            $this->_connectionConfig = $connection;
        } else {
            $this->_connectionConfig = $resourcesConfig->getResourceConnectionConfig(self::DEFAULT_SETUP_CONNECTION);
        }

        $modName = (string)$this->_resourceConfig->setup->module;
        $this->_moduleConfig = $moduleList->getModule($modName);
        $connection = $this->_resourceModel->getConnection($this->_resourceName);
        /**
         * If module setup configuration wasn't loaded
         */
        if (!$connection) {
            $connection = $this->_resourceModel->getConnection($this->_resourceName);
        }
        $this->_conn = $connection;
    }

    /**
     * Get connection object
     *
     * @return Magento_DB_Adapter_Interface
     */
    public function getConnection()
    {
        return $this->_conn;
    }

    /**
     * Add table placeholder/table name relation
     *
     * @param string $tableName
     * @param string $realTableName
     * @return Magento_Core_Model_Resource_Setup
     */
    public function setTable($tableName, $realTableName)
    {
        $this->_tables[$tableName] = $realTableName;
        return $this;
    }

    /**
     * Get table name (validated by db adapter) by table placeholder
     *
     * @param string|array $tableName
     * @return string
     */
    public function getTable($tableName)
    {
        $cacheKey = $this->_getTableCacheName($tableName);
        if (!isset($this->_tables[$cacheKey])) {
            $this->_tables[$cacheKey] = $this->_resourceModel->getTableName($tableName);
        }
        return $this->_tables[$cacheKey];
    }

    /**
     * Retrieve table name for cache
     *
     * @param string|array $tableName
     * @return string
     */
    protected function _getTableCacheName($tableName)
    {
        if (is_array($tableName)) {
            return join('_', $tableName);

        }
        return $tableName;
    }

    /**
     * Get core resource resource model
     *
     * @return Magento_Core_Model_Resource_Resource
     */
    protected function _getResource()
    {
        return Mage::getResourceSingleton('Magento_Core_Model_Resource_Resource');
    }

    /**
     * Apply data updates to the system after upgrading.
     *
     * @return Magento_Core_Model_Resource_Setup
     */
    public function applyDataUpdates()
    {
        $dataVer= $this->_getResource()->getDataVersion($this->_resourceName);
        $configVer = $this->_moduleConfig['version'];
        if ($dataVer !== false) {
            $status = version_compare($configVer, $dataVer);
            if ($status == self::VERSION_COMPARE_GREATER) {
                $this->_upgradeData($dataVer, $configVer);
            }
        } elseif ($configVer) {
            $this->_installData($configVer);
        }
        return $this;
    }

    /**
     * Apply module resource install, upgrade and data scripts
     *
     * @return Magento_Core_Model_Resource_Setup|bool
     */
    public function applyUpdates()
    {
        $dbVer = $this->_getResource()->getDbVersion($this->_resourceName);
        $configVer = $this->_moduleConfig['version'];

        // Module is installed
        if ($dbVer !== false) {
            $status = version_compare($configVer, $dbVer);
            switch ($status) {
                case self::VERSION_COMPARE_LOWER:
                    $this->_rollbackResourceDb($configVer, $dbVer);
                    break;
                case self::VERSION_COMPARE_GREATER:
                    $this->_upgradeResourceDb($dbVer, $configVer);
                    break;
                default:
                    return true;
                    break;
            }
        } elseif ($configVer) {
            $this->_installResourceDb($configVer);
        }

        return $this;
    }

    /**
     * Run data install scripts
     *
     * @param string $newVersion
     * @return Magento_Core_Model_Resource_Setup
     */
    protected function _installData($newVersion)
    {
        $oldVersion = $this->_modifyResourceDb(self::TYPE_DATA_INSTALL, '', $newVersion);
        $this->_modifyResourceDb(self::TYPE_DATA_UPGRADE, $oldVersion, $newVersion);
        $this->_getResource()->setDataVersion($this->_resourceName, $newVersion);

        return $this;
    }

    /**
     * Run data upgrade scripts
     *
     * @param string $oldVersion
     * @param string $newVersion
     * @return Magento_Core_Model_Resource_Setup
     */
    protected function _upgradeData($oldVersion, $newVersion)
    {
        $this->_modifyResourceDb('data-upgrade', $oldVersion, $newVersion);
        $this->_getResource()->setDataVersion($this->_resourceName, $newVersion);

        return $this;
    }

    /**
     * Run resource installation file
     *
     * @param string $newVersion
     * @return Magento_Core_Model_Resource_Setup
     */
    protected function _installResourceDb($newVersion)
    {
        $oldVersion = $this->_modifyResourceDb(self::TYPE_DB_INSTALL, '', $newVersion);
        $this->_modifyResourceDb(self::TYPE_DB_UPGRADE, $oldVersion, $newVersion);
        $this->_getResource()->setDbVersion($this->_resourceName, $newVersion);

        return $this;
    }

    /**
     * Run resource upgrade files from $oldVersion to $newVersion
     *
     * @param string $oldVersion
     * @param string $newVersion
     * @return Magento_Core_Model_Resource_Setup
     */
    protected function _upgradeResourceDb($oldVersion, $newVersion)
    {
        $this->_modifyResourceDb(self::TYPE_DB_UPGRADE, $oldVersion, $newVersion);
        $this->_getResource()->setDbVersion($this->_resourceName, $newVersion);

        return $this;
    }

    /**
     * Roll back resource
     *
     * @param string $newVersion
     * @param string $oldVersion
     * @return Magento_Core_Model_Resource_Setup
     */
    protected function _rollbackResourceDb($newVersion, $oldVersion)
    {
        $this->_modifyResourceDb(self::TYPE_DB_ROLLBACK, $newVersion, $oldVersion);
        return $this;
    }

    /**
     * Uninstall resource
     *
     * @param string $version existing resource version
     * @return Magento_Core_Model_Resource_Setup
     */
    protected function _uninstallResourceDb($version)
    {
        $this->_modifyResourceDb(self::TYPE_DB_UNINSTALL, $version, '');
        return $this;
    }

    /**
     * Retrieve available Database install/upgrade files for current module
     *
     * @param string $actionType
     * @param string $fromVersion
     * @param string $toVersion
     * @return array
     */
    protected function _getAvailableDbFiles($actionType, $fromVersion, $toVersion)
    {
        $resModel   = (string)$this->_connectionConfig->model;
        $modName    = (string)$this->_moduleConfig['name'];

        $filesDir   = $this->_modulesReader->getModuleDir('sql', $modName) . DS . $this->_resourceName;
        if (!is_dir($filesDir) || !is_readable($filesDir)) {
            return array();
        }

        $dbFiles    = array();
        $typeFiles  = array();
        $regExpDb   = sprintf('#^%s-(.*)\.(php|sql)$#i', $actionType);
        $regExpType = sprintf('#^%s-%s-(.*)\.(php|sql)$#i', $resModel, $actionType);
        $handlerDir = dir($filesDir);
        while (false !== ($file = $handlerDir->read())) {
            $matches = array();
            if (preg_match($regExpDb, $file, $matches)) {
                $dbFiles[$matches[1]] = $filesDir . DS . $file;
            } else if (preg_match($regExpType, $file, $matches)) {
                $typeFiles[$matches[1]] = $filesDir . DS . $file;
            }
        }
        $handlerDir->close();

        if (empty($typeFiles) && empty($dbFiles)) {
            return array();
        }

        foreach ($typeFiles as $version => $file) {
            $dbFiles[$version] = $file;
        }

        return $this->_getModifySqlFiles($actionType, $fromVersion, $toVersion, $dbFiles);
    }

    /**
     * Retrieve available Data install/upgrade files for current module
     *
     * @param string $actionType
     * @param string $fromVersion
     * @param string $toVersion
     * @return array
     */
    protected function _getAvailableDataFiles($actionType, $fromVersion, $toVersion)
    {
        $modName    = (string)$this->_moduleConfig['name'];
        $files      = array();

        $filesDir   = Mage::getModuleDir('data', $modName) . DS . $this->_resourceName;
        if (is_dir($filesDir) && is_readable($filesDir)) {
            $regExp     = sprintf('#^%s-(.*)\.php$#i', $actionType);
            $handlerDir = dir($filesDir);
            while (false !== ($file = $handlerDir->read())) {
                $matches = array();
                if (preg_match($regExp, $file, $matches)) {
                    $files[$matches[1]] = $filesDir . DS . $file;
                }
            }
            $handlerDir->close();
        }

        // search data files in old location
        $filesDir   = Mage::getModuleDir('sql', $modName) . DS . $this->_resourceName;
        if (is_dir($filesDir) && is_readable($filesDir)) {
            $regExp     = sprintf('#^%s-%s-(.*)\.php$#i', $this->_connectionConfig->model, $actionType);
            $handlerDir = dir($filesDir);

            while (false !== ($file = $handlerDir->read())) {
                $matches = array();
                if (preg_match($regExp, $file, $matches)) {
                    $files[$matches[1]] = $filesDir . DS . $file;
                }
            }
            $handlerDir->close();
        }

        if (empty($files)) {
            return array();
        }

        return $this->_getModifySqlFiles($actionType, $fromVersion, $toVersion, $files);
    }

    /**
     * Save resource version
     *
     * @param string $actionType
     * @param string $version
     * @return Magento_Core_Model_Resource_Setup
     */
    protected function _setResourceVersion($actionType, $version)
    {
        switch ($actionType) {
            case self::TYPE_DB_INSTALL:
            case self::TYPE_DB_UPGRADE:
                $this->_getResource()->setDbVersion($this->_resourceName, $version);
                break;
            case self::TYPE_DATA_INSTALL:
            case self::TYPE_DATA_UPGRADE:
                $this->_getResource()->setDataVersion($this->_resourceName, $version);
                break;
            default:
                break;
        }

        return $this;
    }

    /**
     * Run module modification files. Return version of last applied upgrade (false if no upgrades applied)
     * @param string $actionType
     * @param string $fromVersion
     * @param string $toVersion
     * @return bool|string
     * @throws Magento_Exception
     */
    protected function _modifyResourceDb($actionType, $fromVersion, $toVersion)
    {
        switch ($actionType) {
            case self::TYPE_DB_INSTALL:
            case self::TYPE_DB_UPGRADE:
                $files = $this->_getAvailableDbFiles($actionType, $fromVersion, $toVersion);
                break;
            case self::TYPE_DATA_INSTALL:
            case self::TYPE_DATA_UPGRADE:
                $files = $this->_getAvailableDataFiles($actionType, $fromVersion, $toVersion);
                break;
            default:
                $files = array();
                break;
        }
        if (empty($files) || !$this->getConnection()) {
            return false;
        }

        $version = false;

        foreach ($files as $file) {
            $fileName = $file['fileName'];
            $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
            $this->getConnection()->disallowDdlCache();
            try {
                switch ($fileType) {
                    case 'php':
                        $result = include $fileName;
                        break;
                    case 'sql':
                        $sql = file_get_contents($fileName);
                        if (!empty($sql)) {

                            $result = $this->run($sql);
                        } else {
                            $result = true;
                        }
                        break;
                    default:
                        $result = false;
                        break;
                }

                if ($result) {
                    $this->_setResourceVersion($actionType, $file['toVersion']);
                    Mage::log($fileName);
                } else {
                    Mage::log("Failed resource setup: {$fileName}");
                }
            } catch (Exception $e) {
                throw new Magento_Exception(sprintf('Error in file: "%s" - %s', $fileName, $e->getMessage()), 0, $e);
            }
            $version = $file['toVersion'];
            $this->getConnection()->allowDdlCache();
        }
        return $version;
    }

    /**
     * Get data files for modifications
     *
     * @param string $actionType
     * @param string $fromVersion
     * @param string $toVersion
     * @param array $arrFiles
     * @return array
     */
    protected function _getModifySqlFiles($actionType, $fromVersion, $toVersion, $arrFiles)
    {
        $arrRes = array();
        switch ($actionType) {
            case self::TYPE_DB_INSTALL:
            case self::TYPE_DATA_INSTALL:
                uksort($arrFiles, 'version_compare');
                foreach ($arrFiles as $version => $file) {
                    if (version_compare($version, $toVersion) !== self::VERSION_COMPARE_GREATER) {
                        $arrRes[0] = array(
                            'toVersion' => $version,
                            'fileName'  => $file
                        );
                    }
                }
                break;

            case self::TYPE_DB_UPGRADE:
            case self::TYPE_DATA_UPGRADE:
                uksort($arrFiles, 'version_compare');
                foreach ($arrFiles as $version => $file) {
                    $versionInfo = explode('-', $version);

                    // In array must be 2 elements: 0 => version from, 1 => version to
                    if (count($versionInfo) !== 2) {
                        break;
                    }
                    $infoFrom = $versionInfo[0];
                    $infoTo   = $versionInfo[1];
                    if (version_compare($infoFrom, $fromVersion) !== self::VERSION_COMPARE_LOWER
                        && version_compare($infoTo, $toVersion) !== self::VERSION_COMPARE_GREATER) {
                        $arrRes[] = array(
                            'toVersion' => $infoTo,
                            'fileName'  => $file
                        );
                    }
                }
                break;

            case self::TYPE_DB_ROLLBACK:
            case self::TYPE_DB_UNINSTALL:
            default:
                break;
        }
        return $arrRes;
    }


    /******************* UTILITY METHODS *****************/

    /**
     * Retrieve row or field from table by id or string and parent id
     *
     * @param string $table
     * @param string $idField
     * @param string|integer $rowId
     * @param string $field
     * @param string $parentField
     * @param string|integer $parentId
     * @return mixed|boolean
     */
    public function getTableRow($table, $idField, $rowId, $field = null, $parentField = null, $parentId = 0)
    {
        $table = $this->getTable($table);
        if (empty($this->_setupCache[$table][$parentId][$rowId])) {
            $adapter = $this->getConnection();
            $bind    = array('id_field' => $rowId);
            $select  = $adapter->select()
                ->from($table)
                ->where($adapter->quoteIdentifier($idField) . '= :id_field');
            if (null !== $parentField) {
                $select->where($adapter->quoteIdentifier($parentField) . '= :parent_id');
                $bind['parent_id'] = $parentId;
            }
            $this->_setupCache[$table][$parentId][$rowId] = $adapter->fetchRow($select, $bind);
        }

        if (null === $field) {
            return $this->_setupCache[$table][$parentId][$rowId];
        }
        return isset($this->_setupCache[$table][$parentId][$rowId][$field])
            ? $this->_setupCache[$table][$parentId][$rowId][$field]
            : false;
    }


    /**
     * Delete table row
     *
     * @param string $table
     * @param string $idField
     * @param string|int $rowId
     * @param null|string $parentField
     * @param int|string $parentId
     * @return Magento_Core_Model_Resource_Setup
     */
    public function deleteTableRow($table, $idField, $rowId, $parentField = null, $parentId = 0)
    {
        $table = $this->getTable($table);
        $adapter = $this->getConnection();
        $where = array($adapter->quoteIdentifier($idField) . '=?' => $rowId);
        if (!is_null($parentField)) {
            $where[$adapter->quoteIdentifier($parentField) . '=?'] = $parentId;
        }

        $adapter->delete($table, $where);

        if (isset($this->_setupCache[$table][$parentId][$rowId])) {
            unset($this->_setupCache[$table][$parentId][$rowId]);
        }

        return $this;
    }

    /**
     * Update one or more fields of table row
     *
     * @param string $table
     * @param string $idField
     * @param string|integer $rowId
     * @param string|array $field
     * @param mixed|null $value
     * @param string $parentField
     * @param string|integer $parentId
     * @return Magento_Eav_Model_Entity_Setup
     */
    public function updateTableRow($table, $idField, $rowId, $field, $value = null, $parentField = null, $parentId = 0)
    {
        $table = $this->getTable($table);
        if (is_array($field)) {
            $data = $field;
        } else {
            $data = array($field => $value);
        }

        $adapter = $this->getConnection();
        $where = array($adapter->quoteIdentifier($idField) . '=?' => $rowId);
        $adapter->update($table, $data, $where);

        if (isset($this->_setupCache[$table][$parentId][$rowId])) {
            if (is_array($field)) {
                $this->_setupCache[$table][$parentId][$rowId] =
                    array_merge($this->_setupCache[$table][$parentId][$rowId], $field);
            } else {
                $this->_setupCache[$table][$parentId][$rowId][$field] = $value;
            }
        }

        return $this;
    }

    /**
     * Check is table exists
     *
     * @param string $table
     * @return bool
     */
    public function tableExists($table)
    {
        $table = $this->getTable($table);
        return $this->getConnection()->isTableExists($table);
    }

    /******************* CONFIG *****************/

    /**
     * Save configuration data
     *
     * @param string $path
     * @param string $value
     * @param int|string $scope
     * @param int $scopeId
     * @return Magento_Core_Model_Resource_Setup
     */
    public function setConfigData($path, $value, $scope = Magento_Core_Model_Store::DEFAULT_CODE, $scopeId = 0)
    {
        $table = $this->getTable('core_config_data');
        // this is a fix for mysql 4.1
        $this->getConnection()->showTableStatus($table);

        $data  = array(
            'scope'     => $scope,
            'scope_id'  => $scopeId,
            'path'      => $path,
            'value'     => $value
        );
        $this->getConnection()->insertOnDuplicate($table, $data, array('value'));
        return $this;
    }

    /**
     * Delete config field values
     *
     * @param string $path
     * @param string $scope (default|stores|websites|config)
     * @return Magento_Core_Model_Resource_Setup
     */
    public function deleteConfigData($path, $scope = null)
    {
        $where = array('path = ?' => $path);
        if (null !== $scope) {
            $where['scope = ?'] = $scope;
        }
        $this->getConnection()->delete($this->getTable('core_config_data'), $where);
        return $this;
    }

    /**
     * Run plain SQL query(ies)
     *
     * @param string $sql
     * @return Magento_Core_Model_Resource_Setup
     */
    public function run($sql)
    {
        $this->getConnection()->multiQuery($sql);
        return $this;
    }

    /**
     * Prepare database before install/upgrade
     *
     * @return Magento_Core_Model_Resource_Setup
     */
    public function startSetup()
    {
        $this->getConnection()->startSetup();
        return $this;
    }

    /**
     * Prepare database after install/upgrade
     *
     * @return Magento_Core_Model_Resource_Setup
     */
    public function endSetup()
    {
        $this->getConnection()->endSetup();
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
    public function getIdxName($tableName, $fields, $indexType = '')
    {
        return $this->_resourceModel->getIdxName($tableName, $fields, $indexType);
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
        return $this->_resourceModel->getFkName($priTableName, $priColumnName, $refTableName, $refColumnName);
    }

    /**
     * Check call afterApplyAllUpdates method for setup class
     *
     * @return boolean
     */
    public function getCallAfterApplyAllUpdates()
    {
        return $this->_callAfterApplyAllUpdates;
    }

    /**
     * Run each time after applying of all updates,
     * if setup model setted  $_callAfterApplyAllUpdates flag to true
     *
     * @return Magento_Core_Model_Resource_Setup
     */
    public function afterApplyAllUpdates()
    {
        return $this;
    }
}
