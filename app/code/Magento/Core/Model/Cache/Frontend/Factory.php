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
 * Factory that creates cache frontend instances based on options
 */
namespace Magento\Core\Model\Cache\Frontend;

class Factory
{
    /**
     * Default cache entry lifetime
     */
    const DEFAULT_LIFETIME = 7200;

    /**
     * @var \Magento\ObjectManager
     */
    private $_objectManager;

    /**
     * @var \Magento\Filesystem
     */
    private $_filesystem;

    /**
     * @var \Magento\App\Dir
     */
    private $_dirs;

    /**
     * Cache options to be enforced for all instances being created
     *
     * @var array
     */
    private $_enforcedOptions = array();

    /**
     * Configuration of decorators that are to be applied to every cache frontend being instantiated, format:
     * array(
     *  array('class' => '<decorator_class>', 'arguments' => array()),
     *  ...
     * )
     *
     * @var array
     */
    private $_decorators = array();

    /**
     * Default cache backend type
     *
     * @var string
     */
    protected $_defaultBackend = 'File';

    /**
     * Options for default backend
     *
     * @var array
     */
    protected $_backendOptions = array(
        'hashed_directory_level'    => 1,
        'hashed_directory_umask'    => 0777,
        'file_name_prefix'          => 'mage',
    );

    /**
     * Resource
     *
     * @var \Magento\App\Resource
     */
    protected $_resource;

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\App\Dir $dirs
     * @param \Magento\App\Resource $resource
     * @param array $enforcedOptions
     * @param array $decorators
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        \Magento\Filesystem $filesystem,
        \Magento\App\Dir $dirs,
        \Magento\App\Resource $resource,
        array $enforcedOptions = array(),
        array $decorators = array()
    ) {
        $this->_objectManager = $objectManager;
        $this->_filesystem = $filesystem;
        $this->_dirs = $dirs;
        $this->_resource = $resource;
        $this->_enforcedOptions = $enforcedOptions;
        $this->_decorators = $decorators;
    }

    /**
     * Return newly created cache frontend instance
     *
     * @param array $options
     * @return \Magento\Cache\FrontendInterface
     */
    public function create(array $options)
    {
        $options = $this->_getExpandedOptions($options);

        foreach (array('backend_options', 'slow_backend_options') as $section) {
            if (!empty($options[$section]['cache_dir'])) {
                $dir = $this->_dirs->getDir(\Magento\App\Dir::VAR_DIR) . DS . $options[$section]['cache_dir'];
                $this->_filesystem->setIsAllowCreateDirectories(true);
                $this->_filesystem->ensureDirectoryExists($dir, 0777);
                $options[$section]['cache_dir'] = $dir;
            }
        }

        $this->_backendOptions['cache_dir'] = $this->_dirs->getDir(\Magento\App\Dir::CACHE);

        $idPrefix = isset($options['id_prefix']) ? $options['id_prefix'] : '';
        if (!$idPrefix && isset($options['prefix'])) {
            $idPrefix = $options['prefix'];
        }
        if (empty($idPrefix)) {
            $idPrefix = substr(md5($this->_dirs->getDir(\Magento\App\Dir::CONFIG)), 0, 3) . '_';
        }
        $options['frontend_options']['cache_id_prefix'] = $idPrefix;

        $backend = $this->_getBackendOptions($options);
        $frontend = $this->_getFrontendOptions($options);

        // Start profiling
        $profilerTags = array(
            'group'         => 'cache',
            'operation'     => 'cache:create',
            'frontend_type' => $frontend['type'],
            'backend_type'  => $backend['type'],
        );
        \Magento\Profiler::start('cache_frontend_create', $profilerTags);

        /** @var $result \Magento\Cache\Frontend\Adapter\Zend */
        $result = $this->_objectManager->create('Magento\Cache\Frontend\Adapter\Zend', array(
            'frontend' => \Zend_Cache::factory(
                $frontend['type'], $backend['type'], $frontend, $backend['options'], true, true, true
            ),
        ));
        $result = $this->_applyDecorators($result);

        // stop profiling
        \Magento\Profiler::stop('cache_frontend_create');
        return $result;
    }

    /**
     * Return options expanded with enforced values
     *
     * @param array $options
     * @return array
     */
    private function _getExpandedOptions(array $options)
    {
        return array_replace_recursive($options, $this->_enforcedOptions);
    }

    /**
     * Apply decorators to a cache frontend instance and return the topmost one
     *
     * @param \Magento\Cache\FrontendInterface $frontend
     * @return \Magento\Cache\FrontendInterface
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    private function _applyDecorators(\Magento\Cache\FrontendInterface $frontend)
    {
        foreach ($this->_decorators as $decoratorConfig) {
            if (!isset($decoratorConfig['class'])) {
                throw new \LogicException('Class has to be specified for a cache frontend decorator.');
            }
            $decoratorClass = $decoratorConfig['class'];
            $decoratorParams = isset($decoratorConfig['parameters']) ? $decoratorConfig['parameters'] : array();
            $decoratorParams['frontend'] = $frontend; // conventionally, 'frontend' argument is a decoration subject
            $frontend = $this->_objectManager->create($decoratorClass, $decoratorParams);
            if (!($frontend instanceof \Magento\Cache\FrontendInterface)) {
                throw new \UnexpectedValueException('Decorator has to implement the cache frontend interface.');
            }
        }
        return $frontend;
    }

    /**
     * Get cache backend options. Result array contain backend type ('type' key) and backend options ('options')
     *
     * @param  array $cacheOptions
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getBackendOptions(array $cacheOptions)
    {
        $enableTwoLevels = false;
        $type = isset($cacheOptions['backend']) ? $cacheOptions['backend'] : $this->_defaultBackend;
        if (isset($cacheOptions['backend_options']) && is_array($cacheOptions['backend_options'])) {
            $options = $cacheOptions['backend_options'];
        } else {
            $options = array();
        }

        $backendType = false;
        switch (strtolower($type)) {
            case 'sqlite':
                if (extension_loaded('sqlite') && isset($options['cache_db_complete_path'])) {
                    $backendType = 'Sqlite';
                }
                break;
            case 'memcached':
                if (extension_loaded('memcached')) {
                    if (isset($cacheOptions['memcached'])) {
                        $options = $cacheOptions['memcached'];
                    }
                    $enableTwoLevels = true;
                    $backendType = 'Libmemcached';
                } elseif (extension_loaded('memcache')) {
                    if (isset($cacheOptions['memcached'])) {
                        $options = $cacheOptions['memcached'];
                    }
                    $enableTwoLevels = true;
                    $backendType = 'Memcached';
                }
                break;
            case 'apc':
                if (extension_loaded('apc') && ini_get('apc.enabled')) {
                    $enableTwoLevels = true;
                    $backendType = 'Apc';
                }
                break;
            case 'xcache':
                if (extension_loaded('xcache')) {
                    $enableTwoLevels = true;
                    $backendType = 'Xcache';
                }
                break;
            case 'eaccelerator':
            case 'varien_cache_backend_eaccelerator':
                if (extension_loaded('eaccelerator') && ini_get('eaccelerator.enable')) {
                    $enableTwoLevels = true;
                    $backendType = 'Magento\Cache\Backend\Eaccelerator';
                }
                break;
            case 'database':
                $backendType = 'Magento\Cache\Backend\Database';
                $options = $this->_getDbAdapterOptions();
                break;
            default:
                if ($type != $this->_defaultBackend) {
                    try {
                        if (class_exists($type, true)) {
                            $implements = class_implements($type, true);
                            if (in_array('Zend_Cache_Backend_Interface', $implements)) {
                                $backendType = $type;
                            }
                        }
                    } catch (\Exception $e) {
                    }
                }
        }

        if (!$backendType) {
            $backendType = $this->_defaultBackend;
            foreach ($this->_backendOptions as $option => $value) {
                if (!array_key_exists($option, $options)) {
                    $options[$option] = $value;
                }
            }
        }

        $backendOptions = array('type' => $backendType, 'options' => $options);
        if ($enableTwoLevels) {
            $backendOptions = $this->_getTwoLevelsBackendOptions($backendOptions, $cacheOptions);
        }
        return $backendOptions;
    }

    /**
     * Get options for database backend type
     *
     * @return array
     */
    protected function _getDbAdapterOptions()
    {
        $options['adapter_callback'] = function () {
            return $this->_resource->getConnection('core_write');
        };
        $options['data_table_callback'] = function () {
            return $this->_resource->getTableName('core_cache');
        };
        $options['tags_table_callback'] = function () {
            return $this->_resource->getTableName('core_cache_tag');
        };
        return $options;
    }

    /**
     * Initialize two levels backend model options
     *
     * @param array $fastOptions fast level backend type and options
     * @param array $cacheOptions all cache options
     * @return array
     */
    protected function _getTwoLevelsBackendOptions($fastOptions, $cacheOptions)
    {
        $options = array();
        $options['fast_backend']                = $fastOptions['type'];
        $options['fast_backend_options']        = $fastOptions['options'];
        $options['fast_backend_custom_naming']  = true;
        $options['fast_backend_autoload']       = true;
        $options['slow_backend_custom_naming']  = true;
        $options['slow_backend_autoload']       = true;

        if (isset($cacheOptions['auto_refresh_fast_cache'])) {
            $options['auto_refresh_fast_cache'] = (bool)$cacheOptions['auto_refresh_fast_cache'];
        } else {
            $options['auto_refresh_fast_cache'] = false;
        }
        if (isset($cacheOptions['slow_backend'])) {
            $options['slow_backend'] = $cacheOptions['slow_backend'];
        } else {
            $options['slow_backend'] = $this->_defaultBackend;
        }
        if (isset($cacheOptions['slow_backend_options'])) {
            $options['slow_backend_options'] = $cacheOptions['slow_backend_options'];
        } else {
            $options['slow_backend_options'] = $this->_backendOptions;
        }
        if ($options['slow_backend'] == 'database') {
            $options['slow_backend'] = 'Magento\Cache\Backend\Database';
            $options['slow_backend_options'] = $this->_getDbAdapterOptions();
            if (isset($cacheOptions['slow_backend_store_data'])) {
                $options['slow_backend_options']['store_data'] = (bool)$cacheOptions['slow_backend_store_data'];
            } else {
                $options['slow_backend_options']['store_data'] = false;
            }
        }

        $backend = array(
            'type'      => 'TwoLevels',
            'options'   => $options
        );
        return $backend;
    }

    /**
     * Get options of cache frontend (options of \Zend_Cache_Core)
     *
     * @param  array $cacheOptions
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getFrontendOptions(array $cacheOptions)
    {
        $options = isset($cacheOptions['frontend_options']) ? $cacheOptions['frontend_options'] : array();
        if (!array_key_exists('caching', $options)) {
            $options['caching'] = true;
        }
        if (!array_key_exists('lifetime', $options)) {
            $options['lifetime'] = isset($cacheOptions['lifetime']) ? $cacheOptions['lifetime']
                : self::DEFAULT_LIFETIME;
        }
        if (!array_key_exists('automatic_cleaning_factor', $options)) {
            $options['automatic_cleaning_factor'] = 0;
        }
        $options['type'] = isset($cacheOptions['frontend']) ? $cacheOptions['frontend'] : 'Magento\Cache\Core';
        return $options;
    }
}
