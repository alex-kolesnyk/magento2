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

class Read implements ReadInterface
{
    /**
     * Directory path
     *
     * @var string
     */
    protected $path;

    /**
     * File factory
     *
     * @var \Magento\Filesystem\File\ReadFactory
     */
    protected $fileFactory;

    /**
     * Filesystem driver
     *
     * @var \Magento\Filesystem\Driver
     */
    protected $driver;

    /**
     * Constructor. Set properties.
     *
     * @param array $config
     * @param \Magento\Filesystem\File\ReadFactory $fileFactory
     * @param \Magento\Filesystem\Driver $driver
     */
    public function __construct
    (
        array $config,
        \Magento\Filesystem\File\ReadFactory $fileFactory,
        \Magento\Filesystem\Driver $driver
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
        if (empty($config['path'])) {
            throw new FilesystemException('Cannot create directory without path');
        }
        $this->path = rtrim($this->fixSeparator($config['path']), '/') . '/';
    }

    /**
     * Retrieves absolute path
     * E.g.: /var/www/application/file.txt
     *
     * @param string $path
     * @return string
     */
    public function getAbsolutePath($path = null)
    {
        return $this->path . ltrim($this->fixSeparator($path), '/');
    }

    /**
     * Retrieves relative path
     *
     * @param string $path
     * @return string
     */
    public function getRelativePath($path = null)
    {
        $path = $this->fixSeparator($path);
        if ((strpos($path, $this->path) === 0) || ($this->path == $path . '/')) {
            $result = substr($path, strlen($this->path));
        } else {
            $result = $path;
        }
        return $result;
    }

    /**
     * Validate of path existence
     *
     * @param string $path
     * @return bool
     * @throws \Magento\Filesystem\FilesystemException
     */
    protected function assertExist($path)
    {
        if ($this->driver->isExists($path) === false) {
            throw new FilesystemException(sprintf('The path "%s" doesn\'t exist', $this->getAbsolutePath($path)));
        }
        return true;
    }

    /**
     * Retrieve list of all entities in given path
     *
     * @param string|null $path
     * @return array
     */
    public function read($path = null)
    {
        $this->assertExist($path);

        $flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
        $iterator = new \FilesystemIterator($this->getAbsolutePath($path), $flags);
        $result = array();
        /** @var \FilesystemIterator $file */
        foreach ($iterator as $file) {
            $result[] = $this->getRelativePath($file->getPathname());
        }
        return $result;
    }

    /**
     * Search all entries for given regex pattern
     *
     * @param string $pattern
     * @param string $path [optional]
     * @return array
     */
    public function search($pattern, $path = null)
    {
        clearstatcache();
        if ($path) {
            $absolutePath = $this->getAbsolutePath($this->getRelativePath($path));
        } else {
            $absolutePath = $this->path;
        }

        $flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
        $iterator = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($absolutePath, $flags)
            ),
            $pattern
        );
        $result = array();
        /** @var \FilesystemIterator $file */
        foreach ($iterator as $file) {
            $result[] = $this->getRelativePath($file->getPathname());
        }
        return $result;
    }

    /**
     * Check a file or directory exists
     *
     * @param null $path
     * @return bool
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function isExist($path = null)
    {
        return $this->driver->isExists($this->getAbsolutePath($path));
    }

    /**
     * Gathers the statistics of the given path
     *
     * @param string $path
     * @return array
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function stat($path)
    {
        $this->assertExist($path);
        return $this->driver->stat($this->getAbsolutePath($path));
    }

    /**
     * Check permissions for reading file or directory
     *
     * @param string $path
     * @return bool
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function isReadable($path)
    {
        return $this->driver->isReadable($this->getAbsolutePath($path));
    }

    /**
     * Open file in read mode
     *
     * @param string $path
     * @return \Magento\Filesystem\File\ReadInterface
     */
    public function openFile($path)
    {
        return $this->fileFactory->create($this->getAbsolutePath($path));
    }

    /**
     * Retrieve file contents from given path
     *
     * @param string $path
     * @return string
     * @throws FilesystemException
     */
    public function readFile($path)
    {
        $absolutePath = $this->getAbsolutePath($path);
        if (!$this->driver->isFile($absolutePath)) {
            throw new FilesystemException(
                sprintf('The file "%s" either doesn\'t exist or not a file', $absolutePath)
            );
        }
        return $this->driver->fileGetContents($absolutePath);
    }

    /**
     * Check whether given path is file
     *
     * @param string $path
     * @return bool
     */
    public function isFile($path)
    {
        return $this->driver->isFile($this->getAbsolutePath($path));
    }

    /**
     * Check whether given path is directory
     *
     * @param string $path
     * @return bool
     */
    public function isDirectory($path)
    {
        return $this->driver->isDirectory($this->getAbsolutePath($path));
    }

    /**
     * Checks is directory contains path
     * Utility method.
     *
     * @param string $path
     * @param string $directory
     * @return bool
     */
    public function isPathInDirectory($path, $directory)
    {
        return 0 === strpos($this->fixSeparator($path), $this->fixSeparator($directory));
    }

    /**
     * Fixes path separator
     * Utility method.
     *
     * @param string $path
     * @return string
     */
    protected function fixSeparator($path)
    {
        return str_replace('\\', '/', $path);
    }
}
