<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Controls configuration factory
 */
class Mage_DesignEditor_Model_Editor_Tools_Controls_Factory
{
    /**#@+
     * Group of types
     */
    const TYPE_QUICK_STYLES = 'quick-style';
    const TYPE_IMAGE_SIZING = 'image-sizing';
    /**#@-*/

    /**
     * File names with
     *
     * @var array
     */
    protected $_fileNames = array(
        self::TYPE_QUICK_STYLES => 'Mage_DesignEditor::controls/quick_styles.xml',
        self::TYPE_IMAGE_SIZING => 'Mage_DesignEditor::controls/image_sizing.xml'
    );

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_View_FileSystem
     */
    protected $_viewFileSystem;

    /**
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Model_View_FileSystem $viewFileSystem
     */
    public function __construct(
        Magento_ObjectManager $objectManager,
        Mage_Core_Model_View_FileSystem $viewFileSystem
    ) {
        $this->_objectManager = $objectManager;
        $this->_viewFileSystem = $viewFileSystem;
    }

    /**
     * Get file path by type
     *
     * @param string $type
     * @param Mage_Core_Model_Theme $theme
     * @return string
     * @throws Magento_Exception
     */
    protected function _getFilePathByType($type, $theme)
    {
        if (!isset($this->_fileNames[$type])) {
            throw new Magento_Exception("Unknown control configuration type: \"{$type}\"");
        }
        return $this->_viewFileSystem->getFilename($this->_fileNames[$type], array(
            'area'       => Mage_Core_Model_Design_PackageInterface::DEFAULT_AREA,
            'themeModel' => $theme
        ));
    }

    /**
     * Create new instance
     *
     * @param string $type
     * @param Mage_Core_Model_Theme $theme
     * @param Mage_Core_Model_Theme $parentTheme
     * @param array $files
     * @throws Magento_Exception
     * @return Mage_DesignEditor_Model_Editor_Tools_Controls_Configuration
     */
    public function create(
        $type,
        Mage_Core_Model_Theme $theme = null,
        Mage_Core_Model_Theme $parentTheme = null,
        array $files = array()
    ) {
        $files[] = $this->_getFilePathByType($type, $theme);
        switch ($type) {
            case self::TYPE_QUICK_STYLES:
                $class = 'Mage_DesignEditor_Model_Config_Control_QuickStyles';
                break;
            case self::TYPE_IMAGE_SIZING:
                $class = 'Mage_DesignEditor_Model_Config_Control_ImageSizing';
                break;
            default:
                throw new Magento_Exception("Unknown control configuration type: \"{$type}\"");
                break;
        }
        /** @var $config Mage_DesignEditor_Model_Config_Control_Abstract */
        $config = $this->_objectManager->create($class, array('configFiles' => $files));
        return Mage::getObjectManager()->create(
            'Mage_DesignEditor_Model_Editor_Tools_Controls_Configuration', array(
                'configuration' => $config,
                'theme'         => $theme,
                'parentTheme'   => $parentTheme
        ));
    }
}
