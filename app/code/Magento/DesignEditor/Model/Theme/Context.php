<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_DesignEditor
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Design editor theme context
 */
namespace Magento\DesignEditor\Model\Theme;

class Context
{
    /**
     * @var \Magento\Core\Model\ThemeFactory
     */
    protected $_themeFactory;

    /**
     * @var \Magento\Theme\Model\CopyService
     */
    protected $_copyService;

    /**
     * @var \Magento\Core\Model\Theme
     */
    protected $_theme;

    /**
     * @var \Magento\Core\Model\Theme
     */
    protected $_stagingTheme;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Core\Model\ThemeFactory $themeFactory
     * @param \Magento\Theme\Model\CopyService $copyService
     */
    public function __construct(
        \Magento\Core\Model\ThemeFactory $themeFactory,
        \Magento\Theme\Model\CopyService $copyService
    ) {
        $this->_themeFactory = $themeFactory;
        $this->_copyService = $copyService;
    }

    /**
     * Reset checked theme
     *
     * @return \Magento\DesignEditor\Model\State
     */
    public function reset()
    {
        $this->_theme = null;
        return $this;
    }

    /**
     * Set theme which will be editable in store designer
     *
     * @param int $themeId
     * @return $this
     * @throws \Magento\Core\Exception
     */
    public function setEditableThemeById($themeId)
    {
        $this->_theme = $this->_themeFactory->create();
        if (!$this->_theme->load($themeId)->getId()) {
            throw new \Magento\Core\Exception(__('We can\'t find theme "%1".', $themeId));
        }
        if ($this->_theme->getType() === \Magento\View\Design\ThemeInterface::TYPE_STAGING) {
            throw new \Magento\Core\Exception(__('Wrong theme type set as editable'));
        }
        return $this;
    }

    /**
     * Get current editable theme
     *
     * @return \Magento\Core\Model\Theme
     * @throws \Magento\Core\Exception
     */
    public function getEditableTheme()
    {
        if (null === $this->_theme) {
            throw new \Magento\Core\Exception(__('Theme has not been set'));
        }
        return $this->_theme;
    }

    /**
     * Get staging theme
     *
     * @return \Magento\Core\Model\Theme
     * @throws \Magento\Core\Exception
     */
    public function getStagingTheme()
    {
        if (null === $this->_stagingTheme) {
            $editableTheme = $this->getEditableTheme();
            if (!$editableTheme->isVirtual()) {
                throw new \Magento\Core\Exception(
                    __('Theme "%1" is not editable.', $editableTheme->getThemeTitle())
                );
            }
            $this->_stagingTheme = $editableTheme->getDomainModel(\Magento\View\Design\ThemeInterface::TYPE_VIRTUAL)
                ->getStagingTheme();
        }
        return $this->_stagingTheme;
    }

    /**
     * Theme which can be rendered on store designer
     *
     * @return \Magento\Core\Model\Theme
     */
    public function getVisibleTheme()
    {
        $editableTheme = $this->getEditableTheme();
        return $editableTheme->isVirtual() ? $this->getStagingTheme() : $editableTheme;
    }

    /**
     * Copy all changed data related to launched theme from staging theme
     *
     * @return $this
     */
    public function copyChanges()
    {
        $this->_copyService->copy($this->getStagingTheme(), $this->getEditableTheme());
        return $this;
    }
}
