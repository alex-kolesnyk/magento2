<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Connect
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Default helper of the module
 *
 * @category    Magento
 * @package     Magento_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Connect_Helper_Data extends Magento_Core_Helper_Data
{
    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * Application dirs
     *
     * @var Magento_Core_Model_Dir
     */
    protected $_dirs;

    /**
     * Core data
     *
     * @var Magento_Core_Helper_Data
     */
    protected $_coreData = null;

    /**
     * @param Magento_Core_Model_Event_Manager $eventManager
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Core_Helper_Http $coreHttp
     * @param Magento_Core_Helper_Context $context
     * @param Magento_Core_Model_Store_Config $coreStoreConfig
     * @param Magento_Core_Model_Config $config
     * @param Magento_Core_Model_Encryption $encryptor
     * @param Magento_Filesystem $filesystem
     * @param Magento_Core_Model_Dir $dirs
     */
    public function __construct(
        Magento_Core_Model_Event_Manager $eventManager,
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Helper_Http $coreHttp,
        Magento_Core_Helper_Context $context,
        Magento_Core_Model_Store_Config $coreStoreConfig,
        Magento_Core_Model_Config $config,
        Magento_Core_Model_Encryption $encryptor,
        Magento_Filesystem $filesystem,
        Magento_Core_Model_Dir $dirs
    ) {
        $this->_coreData = $coreData;
        $this->_dirs = $dirs;
        parent::__construct($eventManager, $coreHttp, $context, $config, $coreStoreConfig, $encryptor);
        $this->_filesystem = $filesystem;
    }

    /**
     * Retrieve file system path for local extension packages
     * Return path with last directory separator
     *
     * @return string
     */
    public function getLocalPackagesPath()
    {
        return $this->_dirs->getDir('var') . DS . 'connect' . DS;
    }

    /**
     * Retrieve file system path for local extension packages (for version 1 packages only)
     * Return path with last directory separator
     *
     * @return string
     */
    public function getLocalPackagesPathV1x()
    {
        return $this->_dirs->getDir('var') . DS . 'pear' . DS;
    }

    /**
     * Retrieve a map to convert a channel from previous version of Magento Connect Manager
     *
     * @return array
     */
    public function getChannelMapFromV1x()
    {
        return array(
            'connect.magentocommerce.com/community' => 'community',
            'connect.magentocommerce.com/core' => 'community'
        );
    }

    /**
     * Retrieve a map to convert a channel to previous version of Magento Connect Manager
     *
     * @return array
     */
    public function getChannelMapToV1x()
    {
        return array(
            'community' => 'connect.magentocommerce.com/community'
        );
    }

    /**
     * Convert package channel in order for it to be compatible with current version of Magento Connect Manager
     *
     * @param string $channel
     *
     * @return string
     */
    public function convertChannelFromV1x($channel)
    {
        $channelMap = $this->getChannelMapFromV1x();
        if (isset($channelMap[$channel])) {
            $channel = $channelMap[$channel];
        }
        return $channel;
    }

    /**
     * Convert package channel in order for it to be compatible with previous version of Magento Connect Manager
     *
     * @param string $channel
     *
     * @return string
     */
    public function convertChannelToV1x($channel)
    {
        $channelMap = $this->getChannelMapToV1x();
        if (isset($channelMap[$channel])) {
            $channel = $channelMap[$channel];
        }
        return $channel;
    }

    /**
     * Load local package data array
     *
     * @param string $packageName without extension
     * @return array|false
     */
    public function loadLocalPackage($packageName)
    {
        //check LFI protection
        $this->checkLfiProtection($packageName);

        $path = $this->getLocalPackagesPath();
        $xmlFile = $path . $packageName . '.xml';
        $serFile = $path . $packageName . '.ser';

        if ($this->_filesystem->isFile($xmlFile) && $this->_filesystem->isReadable($xmlFile)) {
            $xml  = simplexml_load_string($this->_filesystem->read($xmlFile));
            $data = $this->_coreData->xmlToAssoc($xml);
            if (!empty($data)) {
                return $data;
            }
        }

        if ($this->_filesystem->isFile($serFile) && $this->_filesystem->isReadable($xmlFile)) {
            $data = unserialize($this->_filesystem->read($serFile));
            if (!empty($data)) {
                return $data;
            }
        }

        return false;
    }
}
