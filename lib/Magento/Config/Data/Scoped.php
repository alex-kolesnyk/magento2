<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Magento_Config_Data_Scoped extends Magento_Config_Data
{
    /**
     * Configuration scope resolver
     *
     * @var Magento_Config_ScopeInterface
     */
    protected $_configScope;

    /**
     * Configuration reader
     *
     * @var Magento_Config_ReaderInterface
     */
    protected $_reader;

    /**
     * Configuration cache
     *
     * @var Magento_Config_CacheInterface
     */
    protected $_cache;

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheId;

    /**
     * Scope priority loading scheme
     *
     * @var array
     */
    protected $_scopePriorityScheme = array();

    /**
     * Loaded scopes
     *
     * @var array
     */
    protected $_loadedScopes = array();

    /**
     * @param Magento_Config_ReaderInterface $reader
     * @param Magento_Config_ScopeInterface $configScope
     * @param Magento_Config_CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        Magento_Config_ReaderInterface $reader,
        Magento_Config_ScopeInterface $configScope,
        Magento_Config_CacheInterface $cache,
        $cacheId
    ) {
        $this->_reader = $reader;
        $this->_configScope = $configScope;
        $this->_cache = $cache;
        $this->_cacheId = $cacheId;
    }

    /**
     * Get config value by key
     *
     * @param string $path
     * @param mixed $default
     * @return mixed
     */
    public function get($path = null, $default = null)
    {
        $this->_loadScopedData();
        return parent::get($path, $default);
    }

    /**
     * Load data for current scope
     */
    protected function _loadScopedData()
    {
        $scope = $this->_configScope->getCurrentScope();
        if (false == isset($this->_loadedScopes[$scope])) {
            if (false == in_array($scope, $this->_scopePriorityScheme)) {
                $this->_scopePriorityScheme[] = $scope;
            }
            foreach ($this->_scopePriorityScheme as $scopeCode) {
                if (false == isset($this->_loadedScopes[$scopeCode])) {
                    $data = $this->_cache->load($scopeCode . '::' . $this->_cacheId);
                    if (false === $data) {
                        $data = $this->_reader->read($scopeCode);
                        $this->_cache->save(serialize($data), $scopeCode . '::' . $this->_cacheId);
                    }
                    $data = unserialize($data);
                    $this->merge($data);
                    $this->_loadedScopes[$scopeCode] = true;
                }
                if ($scopeCode == $scope) {
                    break;
                }
            }
        }
    }
}
