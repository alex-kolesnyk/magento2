<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Model to create 'staging' copy of 'virtual' theme
 */
class Mage_Core_Model_Theme_Copy_VirtualToStaging extends Mage_Core_Model_Theme_Copy_Abstract
{
    /**
     * Theme model factory
     *
     * @var Mage_Core_Model_Theme_Factory
     */
    protected $_themeFactory;

    /**
     * @param Mage_Core_Model_Theme_Factory $themeFactory
     * @param Mage_Core_Model_Layout_Link $layoutLink
     * @param Mage_Core_Model_Layout_Update $layoutUpdate
     * @param array $data
     */
    public function __construct(
        Mage_Core_Model_Theme_Factory $themeFactory,
        Mage_Core_Model_Layout_Link $layoutLink,
        Mage_Core_Model_Layout_Update $layoutUpdate,
        array $data = array()
    ) {
        $this->_themeFactory = $themeFactory;
        parent::__construct($themeFactory, $layoutLink, $layoutUpdate, $data);
    }

    /**
     * Create 'staging' theme associated with current 'virtual' theme
     *
     * @param Mage_Core_Model_Theme $theme
     * @return Mage_Core_Model_Theme
     * @throws Mage_Core_Exception
     */
    public function copy(Mage_Core_Model_Theme $theme)
    {
        if ($theme->getType() != Mage_Core_Model_Theme::TYPE_VIRTUAL) {
            throw new Mage_Core_Exception(sprintf('Invalid theme of type "%s"', $theme->getType()));
        }
        $stagingTheme = $this->_copyPrimaryData($theme);
        $this->_copyLayoutUpdates($theme, $stagingTheme);
        return $stagingTheme;
    }

    /**
     * Create 'staging' theme that inherits given 'virtual' theme and copies most of it's attributes
     *
     * @param Mage_Core_Model_Theme $theme
     * @return Mage_Core_Model_Theme
     */
    protected function _copyPrimaryData($theme)
    {
        $data = array(
            'parent_id'            => $theme->getId(),
            'theme_path'           => null,
            'theme_version'        => $theme->getThemeVersion(),
            'theme_title'          => sprintf('%s - Staging', $theme->getThemeTitle()),
            'preview_image'        => $theme->getPreviewImage(),
            'magento_version_from' => $theme->getMagentoVersionFrom(),
            'magento_version_to'   => $theme->getMagentoVersionTo(),
            'is_featured'          => $theme->getIsFeatured(),
            'area'                 => $theme->getArea(),
            'type'                 => Mage_Core_Model_Theme::TYPE_STAGING
        );

        $stagingTheme = $this->_themeFactory->create();
        $stagingTheme->setData($data);
        $stagingTheme->save();

        return $stagingTheme;
    }
}
