<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Core_Model_Cache_Config_Reader extends Magento_Config_Reader_Filesystem
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_idAttributes = array(
        '/config/type' => 'name',
    );

    /**
     * @param Mage_Core_Model_Config_Modules_Reader $moduleReader
     * @param Magento_Config_FileResolverInterface $fileResolver
     * @param Mage_Core_Model_Cache_Config_Converter $converter
     * @param Magento_Config_ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param null $schema
     * @param null $perFileSchema
     * @param string $domDocumentClass
     */
    public function __construct(
        Mage_Core_Model_Config_Modules_Reader $moduleReader,
        Magento_Config_FileResolverInterface $fileResolver,
        Mage_Core_Model_Cache_Config_Converter $converter,
        Magento_Config_ValidationStateInterface $validationState,
        $fileName = 'cache.xml',
        $idAttributes = array(),
        $schema = null,
        $perFileSchema = null,
        $domDocumentClass = 'Magento_Config_Dom'
    ) {
        $schema = $schema ?: $moduleReader->getModuleDir('etc', 'Mage_Core') . DIRECTORY_SEPARATOR . 'cache.xsd';
        parent::__construct(
            $fileResolver, $converter, $fileName, $idAttributes, $schema,
            $perFileSchema, $validationState->isValidated(), $domDocumentClass
        );
    }
}
