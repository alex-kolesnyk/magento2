<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Install
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Fylesystem installer
 *
 * @category   Magento
 * @package    Magento_Install
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Install\Model\Installer;

class Filesystem extends \Magento\Install\Model\Installer\AbstractInstaller
{
    /**#@+
     * @deprecated since 1.7.1.0
     */
    const MODE_WRITE = 'write';
    const MODE_READ  = 'read';
    /**#@- */

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * Install Config
     *
     * @var \Magento\Install\Model\Config
     */
    protected $_installConfig;

    /**
     * @var \Magento\App\Dir
     */
    protected $_dir;

    /**
     * Application Root Directory
     *
     * @var string
     */
    protected $_appRootDir;

    /**
     * @param \Magento\Install\Model\Installer $installer
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\Install\Model\Config $installConfig
     * @param \Magento\App\Dir $dir
     */
    public function __construct(
        \Magento\Install\Model\Installer $installer,
        \Magento\Filesystem $filesystem,
        \Magento\Install\Model\Config $installConfig,
        \Magento\App\Dir $dir
    ) {
        parent::__construct($installer);
        $this->_filesystem = $filesystem;
        $this->_installConfig = $installConfig;
    }

    /**
     * Check and prepare file system
     *
     */
    public function install()
    {
        if (!$this->_checkFilesystem()) {
            throw new \Exception();
        };
        return $this;
    }

    /**
     * Check file system by config
     *
     * @return bool
     */
    protected function _checkFilesystem()
    {
        $res = true;
        $config = $this->_installConfig->getWritableFullPathsForCheck();

        if (is_array($config)) {
            foreach ($config as $item) {
                $recursive = isset($item['recursive']) ? (bool)$item['recursive'] : false;
                $existence = isset($item['existence']) ? (bool)$item['existence'] : false;
                $checkRes = $this->_checkFullPath($item['path'], $recursive, $existence);
                $res = $res && $checkRes;
            }
        }
        return $res;
    }

    /**
     * Check file system full path
     *
     * @param  string $fullPath
     * @param  bool $recursive
     * @param  bool $existence
     * @return bool
     */
    protected function _checkFullPath($fullPath, $recursive, $existence)
    {
        $result = true;
        $directory = $this->_filesystem->getDirectoryWrite(\Magento\Filesystem\DirectoryList::ROOT);
        $path = $directory->getRelativePath($fullPath);
        if ($recursive && $directory->isDirectory($path)) {
            $pathsToCheck = $directory->read($path);
            array_unshift($pathsToCheck, $path);
        } else {
            $pathsToCheck = array($path);
        }

        $skipFileNames = array('.svn', '.htaccess');
        foreach ($pathsToCheck as $pathToCheck) {
            if (in_array(basename($pathToCheck), $skipFileNames)) {
                continue;
            }

            if ($existence) {
                $setError = !$directory->isWritable($path);
            } else {
                $setError = $directory->isExist($path) && !$directory->isWritable($path);
            }

            if ($setError) {
                $this->_getInstaller()->getDataModel()->addError(
                    __('Path "%1" must be writable.', $pathToCheck)
                );
                $result = false;
            }
        }

        return $result;
    }
}
