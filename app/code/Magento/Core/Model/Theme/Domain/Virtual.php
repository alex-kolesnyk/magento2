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
 * Virtual theme domain model
 */
namespace Magento\Core\Model\Theme\Domain;

class Virtual implements \Magento\View\Design\Theme\Domain\VirtualInterface
{
    /**
     * Virtual theme model instance
     *
     * @var \Magento\View\Design\ThemeInterface
     */
    protected $_theme;

    /**
     * @var \Magento\Core\Model\ThemeFactory $themeFactory
     */
    protected $_themeFactory;

    /**
     * Staging theme model instance
     *
     * @var \Magento\View\Design\ThemeInterface
     */
    protected $_stagingTheme;

    /**
     * @var \Magento\Core\Model\Theme\CopyService
     */
    protected $_themeCopyService;

    /**
     * Theme customization config
     *
     * @var \Magento\Theme\Model\Config\Customization
     */
    protected $_customizationConfig;

    /**
     * @param \Magento\View\Design\ThemeInterface $theme
     * @param \Magento\Core\Model\ThemeFactory $themeFactory
     * @param \Magento\Core\Model\Theme\CopyService $themeCopyService
     * @param \Magento\Theme\Model\Config\Customization $customizationConfig
     */
    public function __construct(
        \Magento\View\Design\ThemeInterface $theme,
        \Magento\Core\Model\ThemeFactory $themeFactory,
        \Magento\Core\Model\Theme\CopyService $themeCopyService,
        \Magento\Theme\Model\Config\Customization $customizationConfig
    ) {
        $this->_theme = $theme;
        $this->_themeFactory = $themeFactory;
        $this->_themeCopyService = $themeCopyService;
        $this->_customizationConfig = $customizationConfig;
    }

    /**
     * Get 'staging' theme
     *
     * @return \Magento\View\Design\ThemeInterface
     */
    public function getStagingTheme()
    {
        if (!$this->_stagingTheme) {
            $this->_stagingTheme = $this->_theme->getStagingVersion();
            if (!$this->_stagingTheme) {
                $this->_stagingTheme = $this->_createStagingTheme();
                $this->_themeCopyService->copy($this->_theme, $this->_stagingTheme);
            }
        }
        return $this->_stagingTheme;
    }

    /**
     * Get 'physical' theme
     *
     * @return \Magento\View\Design\ThemeInterface
     */
    public function getPhysicalTheme()
    {
        /** @var $parentTheme \Magento\View\Design\ThemeInterface */
        $parentTheme = $this->_theme->getParentTheme();
        while ($parentTheme && !$parentTheme->isPhysical()) {
            $parentTheme = $parentTheme->getParentTheme();
        }

        if (!$parentTheme || !$parentTheme->getId()) {
            return null;
        }

        return $parentTheme;
    }

    /**
     * Check if theme is assigned to ANY store
     *
     * @return bool
     */
    public function isAssigned()
    {
        return $this->_customizationConfig->isThemeAssignedToStore($this->_theme);
    }

    /**
     * Create 'staging' theme associated with current 'virtual' theme
     *
     * @return \Magento\View\Design\ThemeInterface
     */
    protected function _createStagingTheme()
    {
        $stagingTheme = $this->_themeFactory->create();
        $stagingTheme->setData(array(
            'parent_id'            => $this->_theme->getId(),
            'theme_path'           => null,
            'theme_version'        => $this->_theme->getThemeVersion(),
            'theme_title'          => sprintf('%s - Staging', $this->_theme->getThemeTitle()),
            'preview_image'        => $this->_theme->getPreviewImage(),
            'is_featured'          => $this->_theme->getIsFeatured(),
            'type'                 => \Magento\View\Design\ThemeInterface::TYPE_STAGING
        ));
        $stagingTheme->save();
        return $stagingTheme;
    }
}
