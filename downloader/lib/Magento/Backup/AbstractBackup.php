<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     \Magento\Backup
 * @copyright   {copyright}
 * @license     {license_link}
 */
/**
 * Class to work with archives
 *
 * @category    Magento
 * @package     \Magento\Backup
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backup;

abstract class AbstractBackup implements  \Magento\Backup\BackupInterface
{
    /**
     * Backup name
     *
     * @var string
     */
    protected $_name;

    /**
     * Backup creation date
     *
     * @var int
     */
    protected $_time;

    /**
     * Backup file extension
     *
     * @var string
     */
    protected $_backupExtension;

    /**
     * Resource model
     *
     * @var object
     */
    protected $_resourceModel;

    /**
     * Magento's root directory
     *
     * @var string
     */
    protected $_rootDir;

    /**
     * Path to directory where backups stored
     *
     * @var string
     */
    protected $_backupsDir;

    /**
     * Is last operation completed successfully
     *
     * @var bool
     */
    protected $_lastOperationSucceed = false;

    /**
     * Last failed operation error message
     *
     * @var string
     */
    protected $_lastErrorMessage;


    /**
     * Set Backup Extension
     *
     * @param string $backupExtension
     * @return \Magento\Backup\BackupInterface
     */
    public function setBackupExtension($backupExtension)
    {
        $this->_backupExtension = $backupExtension;
        return $this;
    }

    /**
     * Get Backup Extension
     *
     * @return string
     */
    public function getBackupExtension()
    {
        return $this->_backupExtension;
    }

    /**
     * Set Resource Model
     *
     * @param object $resourceModel
     * @return \Magento\Backup\BackupInterface
     */
    public function setResourceModel($resourceModel)
    {
        $this->_resourceModel = $resourceModel;
        return $this;
    }

    /**
     * Get Resource Model
     *
     * @return object
     */
    public function getResourceModel()
    {
        return $this->_resourceModel;
    }

    /**
     * Set Time
     *
     * @param int $time
     * @return \Magento\Backup\BackupInterface
     */
    public function setTime($time)
    {
        $this->_time = $time;
        return $this;
    }

    /**
     * Get Time
     *
     * @return int
     */
    public function getTime()
    {
        return $this->_time;
    }

    /**
     * Set root directory of Magento installation
     *
     * @param string $rootDir
     * @throws \Magento\Exception
     * @return \Magento\Backup\BackupInterface
     */
    public function setRootDir($rootDir)
    {
        if (!is_dir($rootDir)) {
            throw new \Magento\Exception('Bad root directory');
        }

        $this->_rootDir = $rootDir;
        return $this;
    }

    /**
     * Get Magento's root directory
     * @return string
     */
    public function getRootDir()
    {
        return $this->_rootDir;
    }

    /**
     * Set path to directory where backups stored
     *
     * @param string $backupsDir
     * @return \Magento\Backup\BackupInterface
     */
    public function setBackupsDir($backupsDir)
    {
        $this->_backupsDir = $backupsDir;
        return $this;
    }

    /**
     * Get path to directory where backups stored
     *
     * @return string
     */
    public function getBackupsDir()
    {
        return $this->_backupsDir;
    }

    /**
     * Get path to backup
     *
     * @return string
     */
    public function getBackupPath()
    {
        return $this->getBackupsDir() . DS . $this->getBackupFilename();
    }

    /**
     * Get backup file name
     *
     * @return string
     */
    public function getBackupFilename()
    {
        $filename = $this->getTime() . '_' . $this->getType();

        $name = $this->getName();

        if (!empty($name)) {
            $filename .= '_' . $name;
        }

        $filename .= '.' . $this->getBackupExtension();

        return $filename;
    }

    /**
     * Check whether last operation completed successfully
     *
     * @return bool
     */
    public function getIsSuccess()
    {
        return $this->_lastOperationSucceed;
    }

    /**
     * Get last error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_lastErrorMessage;
    }

    /**
     * Set error message
     *
     * @param string $errorMessage
     * @return string
     */
    public function setErrorMessage($errorMessage)
    {
        $this->_lastErrorMessage = $errorMessage;
    }

    /**
     * Set backup name
     *
     * @param string $name
     * @param bool $applyFilter
     * @return \Magento\Backup\BackupInterface
     */
    public function setName($name, $applyFilter = true)
    {
        if ($applyFilter) {
            $name = $this->_filterName($name);
        }
        $this->_name = $name;
        return $this;
    }

    /**
     * Get backup name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get backup display name
     *
     * @return string
     */
    public function getDisplayName()
    {
        return str_replace('_', ' ', $this->_name);
    }

    /**
     * Removes disallowed characters and replaces spaces with underscores
     *
     * @param string $name
     * @return string
     */
    protected function _filterName($name)
    {
        $name = trim(preg_replace('/[^\da-zA-Z ]/', '', $name));
        $name = preg_replace('/\s{2,}/', ' ', $name);
        $name = str_replace(' ', '_', $name);

        return $name;
    }
}
