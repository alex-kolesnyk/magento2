<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Backup Observer
 */
class Magento_Backup_Model_Observer
{
    const XML_PATH_BACKUP_ENABLED          = 'system/backup/enabled';
    const XML_PATH_BACKUP_TYPE             = 'system/backup/type';
    const XML_PATH_BACKUP_MAINTENANCE_MODE = 'system/backup/maintenance';

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * Backup data
     *
     * @var Magento_Backup_Helper_Data
     */
    protected $_backupData = null;

    /**
     * Core registry
     *
     * @var Magento_Core_Model_Registry
     */
    protected $_coreRegistry = null;

    /**
     * Directory model
     *
     * @var Magento_Core_Model_Dir
     */
    protected $_dir;

    /**
     * Construct
     *
     * @param Magento_Backup_Helper_Data $backupData
     * @param Magento_Core_Model_Registry $coreRegistry
     * @param Magento_Core_Model_Dir $dir
     */
    public function __construct(
        Magento_Backup_Helper_Data $backupData,
        Magento_Core_Model_Registry $coreRegistry,
        Magento_Core_Model_Dir $dir
    ) {
        $this->_backupData = $backupData;
        $this->_coreRegistry = $coreRegistry;
        $this->_dir = $dir;
    }

    /**
     * Create Backup
     *
     * @return Magento_Log_Model_Cron
     */
    public function scheduledBackup()
    {
        if (!Mage::getStoreConfigFlag(self::XML_PATH_BACKUP_ENABLED)) {
            return $this;
        }

        if (Mage::getStoreConfigFlag(self::XML_PATH_BACKUP_MAINTENANCE_MODE)) {
            $this->_backupData->turnOnMaintenanceMode();
        }

        $type = Mage::getStoreConfig(self::XML_PATH_BACKUP_TYPE);

        $this->_errors = array();
        try {
            $backupManager = Magento_Backup::getBackupInstance($type)
                ->setBackupExtension($this->_backupData->getExtensionByType($type))
                ->setTime(time())
                ->setBackupsDir($this->_backupData->getBackupsDir());

            $this->_coreRegistry->register('backup_manager', $backupManager);

            if ($type != Magento_Backup_Helper_Data::TYPE_DB) {
                $backupManager->setRootDir($this->_dir->getDir(Magento_Core_Model_Dir::ROOT))
                    ->addIgnorePaths($this->_backupData->getBackupIgnorePaths());
            }

            $backupManager->create();
            Mage::log($this->_backupData->getCreateSuccessMessageByType($type));
        } catch (Exception $e) {
            $this->_errors[] = $e->getMessage();
            $this->_errors[] = $e->getTrace();
            Mage::log($e->getMessage(), Zend_Log::ERR);
            Mage::logException($e);
        }

        if (Mage::getStoreConfigFlag(self::XML_PATH_BACKUP_MAINTENANCE_MODE)) {
            $this->_backupData->turnOffMaintenanceMode();
        }

        return $this;
    }
}
