<?php
/**
 * Module configuration file reader
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Core_Model_Config_Modules_Reader
{
    /**
     * Modules configuration
     *
     * @var Mage_Core_Model_Config_Modules
     */
    protected $_config;

    /**
     * Module file reader
     *
     * @var Mage_Core_Model_Config_Loader_Modules_File
     */
    protected $_fileReader;

    /**
     * @param Mage_Core_Model_Config_Modules $modulesConfig
     * @param Mage_Core_Model_Config_Loader_Modules_File $fileReader
     */
    public function __construct(
        Mage_Core_Model_Config_Modules $modulesConfig,
        Mage_Core_Model_Config_Loader_Modules_File $fileReader
    ) {
        $this->_config = $modulesConfig;
        $this->_fileReader = $fileReader;
    }

    /**
     * Iterate all active modules "etc" folders and combine data from
     * specidied xml file name to one object
     *
     * @param   string $fileName
     * @param   null|Mage_Core_Model_Config_Base $mergeToObject
     * @param   null|Mage_Core_Model_Config_Base $mergeModel
     * @return  Mage_Core_Model_Config_Base
     */
    public function loadModulesConfiguration($fileName, $mergeToObject = null, $mergeModel = null)
    {
        return $this->_fileReader->loadConfigurationFromFile($this->_config, $fileName, $mergeToObject, $mergeModel);
    }

    /**
     * Go through all modules and find configuration files of active modules
     *
     * @param string $filename
     * @return array
     */
    public function getModuleConfigurationFiles($filename)
    {
        return $this->_fileReader->getConfigurationFiles($this->_config, $filename);
    }

    /**
     * Get module directory by directory type
     *
     * @param   string $type
     * @param   string $moduleName
     * @return  string
     */
    public function getModuleDir($type, $moduleName)
    {
        return $this->_fileReader->getModuleDir($this->_config, $type, $moduleName);
    }
}