<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Theme
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Theme controller
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design;

class Theme extends \Magento\Backend\Controller\Adminhtml\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Controller\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\Controller\Context $context,
        \Magento\Core\Model\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_eventManager->dispatch('theme_registration_from_filesystem');
        $this->loadLayout();
        $this->_setActiveMenu('Magento_Theme::system_design_theme');
        $this->renderLayout();
    }

    /**
     * Grid ajax action
     */
    public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     * Create new theme
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit theme
     */
    public function editAction()
    {
        $themeId = (int)$this->getRequest()->getParam('id');
        /** @var $theme \Magento\View\Design\ThemeInterface */
        $theme = $this->_objectManager->create('Magento\View\Design\ThemeInterface');
        try {
            $theme->setType(\Magento\View\Design\ThemeInterface::TYPE_VIRTUAL);
            if ($themeId && (!$theme->load($themeId)->getId() || !$theme->isVisible())) {
                throw new \Magento\Core\Exception(__('We cannot find theme "%1".', $themeId));
            }
            $this->_coreRegistry->register('current_theme', $theme);

            $this->loadLayout();
            /** @var $tab \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab_Css */
            $tab = $this->getLayout()->getBlock('theme_edit_tabs_tab_css_tab');
            if ($tab && $tab->canShowTab()) {
                /** @var $helper \Magento\Core\Helper\Theme */
                $helper = $this->_objectManager->get('Magento\Core\Helper\Theme');
                $files = $helper->getGroupedCssFiles($theme);
                $tab->setFiles($files);
            }
            $this->_setActiveMenu('Magento_Theme::system_design_theme');
            $this->renderLayout();
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('adminhtml/*/');
        } catch (\Exception $e) {
            $this->_getSession()->addError(__('We cannot find the theme.'));
            $this->_objectManager->get('Magento\Logger')->logException($e);
            $this->_redirect('adminhtml/*/');
        }
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        $redirectBack = (bool)$this->getRequest()->getParam('back', false);
        $themeData = $this->getRequest()->getParam('theme');
        $customCssData = $this->getRequest()->getParam('custom_css_content');
        $removeJsFiles = (array)$this->getRequest()->getParam('js_removed_files');
        $reorderJsFiles = array_keys($this->getRequest()->getParam('js_order', array()));

        /** @var $themeFactory \Magento\View\Design\Theme\FlyweightFactory */
        $themeFactory = $this->_objectManager->get('Magento\View\Design\Theme\FlyweightFactory');
        /** @var $cssService \Magento\Theme\Model\Theme\Customization\File\CustomCss */
        $cssService = $this->_objectManager->get('Magento\Theme\Model\Theme\Customization\File\CustomCss');
        /** @var $singleFile \Magento\Theme\Model\Theme\SingleFile */
        $singleFile = $this->_objectManager->create('Magento\Theme\Model\Theme\SingleFile',
            array('fileService' => $cssService));
        try {
            if ($this->getRequest()->getPost()) {
                if (!empty($themeData['theme_id'])) {
                    $theme = $themeFactory->create($themeData['theme_id']);
                } else {
                    $parentTheme = $themeFactory->create($themeData['parent_id']);
                    $theme = $parentTheme->getDomainModel(\Magento\View\Design\ThemeInterface::TYPE_PHYSICAL)
                        ->createVirtualTheme($parentTheme);
                }
                if ($theme && !$theme->isEditable()) {
                    throw new \Magento\Core\Exception(__('Theme isn\'t editable.'));
                }
                $theme->addData($themeData);
                if (isset($themeData['preview']['delete'])) {
                    $theme->getThemeImage()->removePreviewImage();
                }
                $theme->getThemeImage()->uploadPreviewImage('preview');
                $theme->setType(\Magento\View\Design\ThemeInterface::TYPE_VIRTUAL);
                $theme->save();
                $customization = $theme->getCustomization();
                $customization->reorder(\Magento\View\Design\Theme\Customization\File\Js::TYPE, $reorderJsFiles);
                $customization->delete($removeJsFiles);
                $singleFile->update($theme, $customCssData);
                $this->_getSession()->addSuccess(__('You saved the theme.'));
            }
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_getSession()->setThemeData($themeData);
            $this->_getSession()->setThemeCustomCssData($customCssData);
            $redirectBack = true;
        } catch (\Exception $e) {
            $this->_getSession()->addError('The theme was not saved');
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
        $redirectBack
            ? $this->_redirect('adminhtml/*/edit', array('id' => $theme->getId()))
            : $this->_redirect('adminhtml/*/');
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        $redirectBack = (bool)$this->getRequest()->getParam('back', false);
        $themeId = $this->getRequest()->getParam('id');
        try {
            if ($themeId) {
                /** @var $theme \Magento\View\Design\ThemeInterface */
                $theme = $this->_objectManager->create('Magento\View\Design\ThemeInterface')->load($themeId);
                if (!$theme->getId()) {
                    throw new \InvalidArgumentException(sprintf('We cannot find a theme with id "%1".', $themeId));
                }
                if (!$theme->isVirtual()) {
                    throw new \InvalidArgumentException(
                        sprintf('Only virtual theme is possible to delete and theme "%s" isn\'t virtual', $themeId)
                    );
                }
                $theme->delete();
                $this->_getSession()->addSuccess(__('You deleted the theme.'));
            }
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('We cannot delete the theme.'));
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
        /**
         * @todo Temporary solution. Theme module should not know about the existence of editor module.
         */
        $redirectBack ? $this->_redirect('adminhtml/system_design_editor/index/') : $this->_redirect('adminhtml/*/');
    }

    /**
     * Upload css file
     */
    public function uploadCssAction()
    {
        /** @var $serviceModel \Magento\Theme\Model\Uploader\Service */
        $serviceModel = $this->_objectManager->get('Magento\Theme\Model\Uploader\Service');
        try {
            $cssFileContent = $serviceModel->uploadCssFile('css_file_uploader');
            $result = array('error' => false, 'content' => $cssFileContent['content']);
        } catch (\Magento\Core\Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
        } catch (\Exception $e) {
            $result = array('error' => true, 'message' => __('We cannot upload the CSS file.'));
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result));
    }

    /**
     * Upload js file
     *
     * @throws \Magento\Core\Exception
     */
    public function uploadJsAction()
    {
        $themeId = $this->getRequest()->getParam('id');
        /** @var $serviceModel \Magento\Theme\Model\Uploader\Service */
        $serviceModel = $this->_objectManager->get('Magento\Theme\Model\Uploader\Service');
        /** @var $themeFactory \Magento\View\Design\Theme\FlyweightFactory */
        $themeFactory = $this->_objectManager->get('Magento\View\Design\Theme\FlyweightFactory');
        /** @var $jsService \Magento\View\Design\Theme\Customization\File\Js */
        $jsService = $this->_objectManager->get('Magento\View\Design\Theme\Customization\File\Js');
        try {
            $theme = $themeFactory->create($themeId);
            if (!$theme) {
                throw new \Magento\Core\Exception(__('We cannot find a theme with id "%1".', $themeId));
            }
            $jsFileData = $serviceModel->uploadJsFile('js_files_uploader');
            $jsFile = $jsService->create();
            $jsFile->setTheme($theme);
            $jsFile->setFileName($jsFileData['filename']);
            $jsFile->setData('content', $jsFileData['content']);
            $jsFile->save();

            /** @var $customization \Magento\View\Design\Theme\Customization */
            $customization = $this->_objectManager->create('Magento\View\Design\Theme\CustomizationInterface',
                array('theme' => $theme));
            $customJsFiles = $customization->getFilesByType(\Magento\View\Design\Theme\Customization\File\Js::TYPE);
            $result = array('error' => false, 'files' => $customization->generateFileInfo($customJsFiles));
        } catch (\Magento\Core\Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
        } catch (\Exception $e) {
            $result = array('error' => true, 'message' => __('We cannot upload the JS file.'));
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result));
    }

    /**
     * Download custom css file
     */
    public function downloadCustomCssAction()
    {
        $themeId = $this->getRequest()->getParam('theme_id');
        try {
            /** @var $themeFactory \Magento\View\Design\Theme\FlyweightFactory */
            $themeFactory = $this->_objectManager->create('Magento\View\Design\Theme\FlyweightFactory');
            $theme = $themeFactory->create($themeId);
            if (!$theme) {
                throw new \InvalidArgumentException(sprintf('We cannot find a theme with id "%1".', $themeId));
            }

            $customCssFiles = $theme->getCustomization()->getFilesByType(
                \Magento\Theme\Model\Theme\Customization\File\CustomCss::TYPE
            );
            /** @var $customCssFile \Magento\View\Design\Theme\FileInterface */
            $customCssFile = reset($customCssFiles);
            if ($customCssFile && $customCssFile->getContent()) {
                $this->_prepareDownloadResponse(
                    $customCssFile->getFileName(),
                    array(
                        'type'  => 'filename',
                        'value' => $customCssFile->getFullPath()
                    )
                );
            }
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('We cannot find file'));
            $this->_redirectUrl($this->_getRefererUrl());
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
    }

    /**
     * Download css file
     */
    public function downloadCssAction()
    {
        $themeId = $this->getRequest()->getParam('theme_id');
        $file = $this->getRequest()->getParam('file');

        /** @var $helper \Magento\Core\Helper\Theme */
        $helper = $this->_objectManager->get('Magento\Core\Helper\Theme');
        $fileName = $helper->urlDecode($file);
        try {
            /** @var $theme \Magento\View\Design\ThemeInterface */
            $theme = $this->_objectManager->create('Magento\View\Design\ThemeInterface')->load($themeId);
            if (!$theme->getId()) {
                throw new \InvalidArgumentException(sprintf('We cannot find a theme with id "%1".', $themeId));
            }

            $themeCss = $helper->getCssFiles($theme);
            if (!isset($themeCss[$fileName])) {
                throw new \InvalidArgumentException(
                    sprintf('Css file "%s" is not in the theme with id "%d".', $fileName, $themeId)
                );
            }

            $this->_prepareDownloadResponse($fileName, array(
                'type'  => 'filename',
                'value' => $themeCss[$fileName]['path']
            ));
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('We cannot find file "%1".', $fileName));
            $this->_redirectUrl($this->_getRefererUrl());
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
    }

    /**
     * Check the permission to manage themes
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Theme::theme');
    }
}
