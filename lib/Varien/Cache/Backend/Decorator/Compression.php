<?php
/**
 * {license_notice}
 *
 * @category   Varien
 * @package    Varien_Cache
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Decorator class for compressing data before storing in cache
 */
class Varien_Cache_Backend_Decorator_Compression extends Varien_Cache_Backend_Decorator_DecoratorAbstract
{
    /**
     * Prefix of compressed strings
     */
    const COMPRESSION_PREFIX = 'CACHE_COMPRESSION';

    /**
     * Array of specific options. Made in separate array to distinguish from parent options
     * @var array
     */
    protected $_decoratorOptions = array(
        'compression_threshold' => 512
    );

    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * Note : return value is always "string" (unserialization is done by the core not by the backend)
     *
     * @param  string  $cacheId                Cache id
     * @param  boolean $noTestCacheValidity    If set to true, the cache validity won't be tested
     * @return string|false cached datas
     */
    public function load($cacheId, $noTestCacheValidity = false)
    {
        //decompression
        $data = $this->_backend->load($cacheId, $noTestCacheValidity);

        if ($this->_isDecompressionNeeded($data)) {
            $data = self::_decompressData($data);
        }

        return $data;
    }

    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param  string $data             Datas to cache
     * @param  string $cacheId          Cache id
     * @param  array  $tags             Array of strings, the cache record will be tagged by each string entry
     * @param  bool   $specificLifetime If != false, set a specific lifetime for this cache record
     *                                  (null => infinite lifetime)
     * @param  int    $priority         integer between 0 (very low priority) and 10 (maximum priority) used by
     *                                  some particular backends
     * @return boolean true if no problem
     */
    public function save($data, $cacheId, $tags = array(), $specificLifetime = false, $priority = 8)
    {
        if ($this->_isCompressionNeeded($data)) {
            $data = self::_compressData($data);
        }

        return $this->_backend->save($data, $cacheId, $tags, $specificLifetime, $priority);
    }

    /**
     * Compress data and add specific prefix
     *
     * @param string $data
     * @return string
     */
    protected static function _compressData($data)
    {
        return self::COMPRESSION_PREFIX . gzcompress($data);
    }

    /**
     * Get whether compression is needed
     *
     * @param string $data
     * @return bool
     */
    protected function _isCompressionNeeded($data)
    {
        return (strlen($data) > (int)$this->_decoratorOptions['compression_threshold']);
    }

    /**
     * Remove special prefix and decompress data
     *
     * @param string $data
     * @return string
     */
    protected static function _decompressData($data)
    {
        return gzuncompress(substr($data, strlen(self::COMPRESSION_PREFIX)));
    }

    /**
     * Get whether decompression is needed
     *
     * @param string $data
     * @return bool
     */
    protected function _isDecompressionNeeded($data)
    {
        return (strpos($data, self::COMPRESSION_PREFIX) === 0);
    }

}
