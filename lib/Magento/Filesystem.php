<?php
/**
 * Magento filesystem facade
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento;

use Magento\Filesystem\FilesystemException;

class Filesystem
{
    /**
     * Code base root
     */
    const ROOT = 'base';

    /**
     * Most of entire application
     */
    const APP = 'app';

    /**
     * Modules
     */
    const MODULES = 'code';

    /**
     * Themes
     */
    const THEMES = 'design';

    /**
     * Initial configuration of the application
     */
    const CONFIG = 'etc';

    /**
     * Libraries or third-party components
     */
    const LIB = 'lib';

    /**
     * Files with translation of system labels and messages from en_US to other languages
     */
    const LOCALE = 'i18n';

    /**
     * \Directory within document root of a web-server to access static view files publicly
     */
    const PUB = 'pub';

    /**
     * Libraries/components that need to be accessible publicly through web-server (such as various DHTML components)
     */
    const PUB_LIB = 'pub_lib';

    /**
     * Storage of files entered or generated by the end-user
     */
    const MEDIA = 'media';

    /**
     * Storage of static view files that are needed on HTML-pages, emails or similar content
     */
    const STATIC_VIEW = 'static';

    /**
     * Public view files, stored to avoid repetitive run-time calculation, and can be re-generated any time
     */
    const PUB_VIEW_CACHE = 'view_cache';

    /**
     * Various files generated by the system in runtime
     */
    const VAR_DIR = 'var';

    /**
     * Temporary files
     */
    const TMP = 'tmp';

    /**
     * File system caching directory (if file system caching is used)
     */
    const CACHE = 'cache';

    /**
     * Logs of system messages and errors
     */
    const LOG = 'log';

    /**
     * File system session directory (if file system session storage is used)
     */
    const SESSION = 'session';

    /**
     * Dependency injection related file directory
     *
     */
    const DI = 'di';

    /**
     * Relative directory key for generated code
     */
    const GENERATION = 'generation';

    /**
     * Temporary directory for uploading files by end-user
     */
    const UPLOAD = 'upload';

    /**
     * Virtual directory to access files via socket connections (using http or https schemes)
     */
    const SOCKET = 'socket';

    /**
     * System base temporary folder
     */
    const SYS_TMP = 'sys_tmp';

    /**
     * @var \Magento\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var \Magento\Filesystem\Directory\WriteFactory
     */
    protected $writeFactory;

    /**
     * @var \Magento\Filesystem\Directory\ReadInterface[]
     */
    protected $readInstances = array();

    /**
     * @var \Magento\Filesystem\Directory\WriteInterface[]
     */
    protected $writeInstances = array();

    /**
     * @deprecated
     */
    const DIRECTORY_SEPARATOR = '/';

use \Magento\FilesystemDeprecated;

    /**
     * @param Filesystem\DirectoryList $directoryList
     * @param Filesystem\Directory\ReadFactory $readFactory
     * @param Filesystem\Directory\WriteFactory $writeFactory
     * @param Filesystem\AdapterInterface $adapter
     */
    public function __construct(
        \Magento\Filesystem\DirectoryList $directoryList,
        \Magento\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Filesystem\Directory\WriteFactory $writeFactory,
        \Magento\Filesystem\AdapterInterface $adapter
    ) {
        $this->directoryList = $directoryList;
        $this->readFactory = $readFactory;
        $this->writeFactory = $writeFactory;

        $this->_adapter = $adapter;

    }


    /**
     * Create an instance of directory with write permissions
     *
     * @param string $code
     * @return \Magento\Filesystem\Directory\ReadInterface
     */
    public function getDirectoryRead($code)
    {
        if (!array_key_exists($code, $this->readInstances)) {
            $config = $this->directoryList->getConfig($code);
            $this->readInstances[$code] = $this->readFactory->create($config);
        }
        return $this->readInstances[$code];
    }

    /**
     * Create an instance of directory with read permissions
     *
     * @param string $code
     * @return \Magento\Filesystem\Directory\WriteInterface
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function getDirectoryWrite($code)
    {
        if (!array_key_exists($code, $this->writeInstances)) {
            $config = $this->directoryList->getConfig($code);
            if (isset($config['read_only']) && $config['read_only']) {
                throw new FilesystemException(sprintf('The "%s" directory doesn\'t allow write operations', $code));
            }

            $this->writeInstances[$code] = $this->writeFactory->create($config);
        }
        return $this->writeInstances[$code];
    }

    /**
     * Retrieve absolute path for for given code
     *
     * @param string $code
     * @return string
     */
    public function getPath($code = self::ROOT)
    {
        $config = $this->directoryList->getConfig($code);
        $path = isset($config['path']) ? $config['path'] : '';
        return str_replace('\\', '/', $path);
    }

    /**
     * Retrieve uri for given code
     *
     * @param string $code
     * @return string
     */
    public function getUri($code)
    {
        $config = $this->directoryList->getConfig($code);
        return isset($config['uri']) ? $config['uri'] : '';
    }

}
