<?php
/**
 * System configuration schema locator
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Backend_Model_Config_SchemaLocator implements Magento_Config_SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $_schema = null;

    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     */
    protected $_perFileSchema = null;

    /**
     * @param Mage_Core_Model_Config_Modules_Reader $moduleReader
     */
    public function __construct(Mage_Core_Model_Config_Modules_Reader $moduleReader)
    {
        $this->_schema = $moduleReader->getModuleDir('etc', 'Mage_Backend')
            . DIRECTORY_SEPARATOR . 'system.xsd';

        $this->_perFileSchema = $moduleReader->getModuleDir('etc', 'Mage_Backend')
            . DIRECTORY_SEPARATOR . 'system_file.xsd';
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Get path to pre file validation schema
     *
     * @return string|null
     */
    public function getPerFileSchema()
    {
        return $this->_perFileSchema;
    }
}
