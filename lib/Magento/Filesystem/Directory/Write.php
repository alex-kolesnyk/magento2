<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Filesystem\Directory;

use Magento\Filesystem\FilesystemException;

class Write extends Read implements WriteInterface
{
    /**
     * Is directory creation
     *
     * @var bool
     */
    protected $allowCreateDirs;

    /**
     * Permissions for new directories and files
     *
     * @var int
     */
    protected $permissions = 0777;

    /**
     * Constructor
     *
     * @param array $config
     * @param \Magento\Filesystem\File\WriteFactory $fileFactory
     * @param \Magento\Filesystem\DriverInterface $driver
     */
    public function __construct
    (
        array $config,
        \Magento\Filesystem\File\WriteFactory $fileFactory,
        \Magento\Filesystem\DriverInterface $driver
    ) {
        $this->setProperties($config);
        $this->fileFactory = $fileFactory;

        $this->driver = $driver;
    }

    /**
     * Set properties from config
     *
     * @param array $config
     * @throws \Magento\Filesystem\FilesystemException
     */
    protected function setProperties(array $config)
    {
        parent::setProperties($config);
        if (isset($config['permissions'])) {
            $this->permissions = $config['permissions'];
        }
        if (isset($config['allow_create_dirs'])) {
            $this->allowCreateDirs = (bool) $config['allow_create_dirs'];
        }
    }

    /**
     * Check if directory is writable
     *
     * @throws \Magento\Filesystem\FilesystemException
     */
    protected function assertWritable($path)
    {
        if ($this->isWritable($path) === false) {
            throw new FilesystemException(sprintf('The path "%s" is not writable', $this->getAbsolutePath($path)));
        }
    }

    /**
     * Check if given path is exists and is file
     *
     * @param string $path
     * @throws \Magento\Filesystem\FilesystemException
     */
    protected function assertIsFile($path)
    {
        clearstatcache();
        $absolutePath = $this->getAbsolutePath($path);
        if (!$this->driver->isFile($absolutePath)) {
            throw new FilesystemException(sprintf('The "%s" file doesn\'t exist or not a file', $absolutePath));
        }
    }

    /**
     * Create directory if it does not exists
     *
     * @param string $path
     * @return bool
     * @throws FilesystemException
     */
    public function create($path = null)
    {
        $absolutePath = $this->getAbsolutePath($path);
        if ($this->driver->isDirectory($absolutePath)) {
            return true;
        }
        return $this->driver->createDirectory($absolutePath, $this->permissions);
    }

    /**
     * Rename a file
     *
     * @param string $path
     * @param string $newPath
     * @param WriteInterface $targetDirectory
     * @return bool
     * @throws FilesystemException
     */
    public function renameFile($path, $newPath, WriteInterface $targetDirectory = null)
    {
        $this->assertIsFile($path);
        $targetDirectory = $targetDirectory ? : $this;
        if (!$targetDirectory->isExist($this->driver->getParentDirectory($newPath))) {
            $targetDirectory->create($this->driver->getParentDirectory($newPath));
        }
        $absolutePath = $this->getAbsolutePath($path);
        $absoluteNewPath = $targetDirectory->getAbsolutePath($newPath);
        $result = $this->driver->rename($absolutePath, $absoluteNewPath);
        if (!$result) {
            throw new FilesystemException(
                sprintf('The "%s" path cannot be renamed into "%s"', $absolutePath, $absoluteNewPath)
            );
        }
        return $result;
    }

    /**
     * Copy a file
     *
     * @param string $path
     * @param string $destination
     * @param WriteInterface $targetDirectory
     * @return bool
     * @throws FilesystemException
     */
    public function copyFile($path, $destination, WriteInterface $targetDirectory = null)
    {
        $this->assertIsFile($path);

        $targetDirectory = $targetDirectory ? : $this;
        if (!$targetDirectory->isExist($this->driver->getParentDirectory($destination))) {
            $targetDirectory->create($this->driver->getParentDirectory($destination));
        }
        $absolutePath = $this->getAbsolutePath($path);
        $absoluteDestination = $targetDirectory->getAbsolutePath($destination);

        $result = $this->driver->copy($absolutePath, $absoluteDestination);
        if (!$result) {
            throw new FilesystemException(
                sprintf('The "%s" path cannot be renamed into "%s"', $absolutePath, $absoluteDestination)
            );
        }
        return $result;
    }

    /**
     * Delete given path
     *
     * @param string $path
     * @return bool
     * @throws FilesystemException
     */
    public function delete($path = null)
    {
        $this->assertExist($path);
        $absolutePath = $this->getAbsolutePath($path);
        if ($this->driver->isFile($absolutePath)) {
            $this->driver->deleteFile($absolutePath);
        } else {
            foreach ($this->read($path) as $subPath) {
                $this->delete($subPath);
            }
            $this->driver->deleteDirectory($absolutePath);
        }
        return true;
    }

    /**
     * Change permissions of given path
     *
     * @param string $path
     * @param int $permissions
     * @return bool
     * @throws FilesystemException
     */
    public function changePermissions($path, $permissions)
    {
        $this->assertExist($path);
        $absolutePath = $this->getAbsolutePath($path);
        return $this->driver->changePermissions($absolutePath, $permissions);
    }

    /**
     * Sets access and modification time of file.
     *
     * @param string $path
     * @param int|null $modificationTime
     * @return bool
     * @throws FilesystemException
     */
    public function touch($path, $modificationTime = null)
    {
        $folder = $this->driver->getParentDirectory($path);
        $this->create($folder);
        $this->assertWritable($folder);
        return $this->driver->touch($this->getAbsolutePath($path), $modificationTime);
    }

    /**
     * Check if given path is writable
     *
     * @param null $path
     * @return bool
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function isWritable($path = null)
    {
        return $this->driver->isWritable($this->getAbsolutePath($path));
    }

    /**
     * Open file in given mode
     *
     * @param string $path
     * @param string $mode
     * @return \Magento\Filesystem\File\WriteInterface
     */
    public function openFile($path, $mode = 'w')
    {
        $absolutePath = $this->getAbsolutePath($path);
        $folder = $this->driver->getParentDirectory($absolutePath);
        $this->create($folder);
        $this->assertWritable($folder);
        return $this->fileFactory->create($absolutePath, $this->driver, $mode);
    }

    /**
     * Open file in given path
     *
     * @param string $path
     * @param string $content
     * @param string|null $mode
     * @return int The number of bytes that were written.
     * @throws FilesystemException
     */
    public function writeFile($path, $content, $mode = null)
    {
        $absolutePath = $this->getAbsolutePath($path);
        $folder = $this->getRelativePath($this->driver->getParentDirectory($absolutePath));
        $this->create($folder);
        $this->assertWritable($folder);
        return $this->driver->filePutContents($absolutePath, $content, $mode);
    }
}
