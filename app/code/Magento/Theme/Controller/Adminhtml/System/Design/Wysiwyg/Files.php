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
 * Files controller
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg;

class Files extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }
    
    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_view->loadLayout('overlay_popup');
        $this->_view->renderLayout();
    }

    /**
     * Tree json action
     */
    public function treeJsonAction()
    {
        try {
            $this->getResponse()->setBody(
                $this->_view->getLayout()->createBlock('Magento\Theme\Block\Adminhtml\Wysiwyg\Files\Tree')
                    ->getTreeJson($this->_getStorage()->getTreeArray())
            );
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Logger')->logException($e);
            $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(array()));
        }
    }

    /**
     * New folder action
     */
    public function newFolderAction()
    {
        $name = $this->getRequest()->getPost('name');
        try {
            $path = $this->_getSession()->getStoragePath();
            $result = $this->_getStorage()->createFolder($name, $path);
        } catch (\Magento\Core\Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
        } catch (\Exception $e) {
            $result = array('error' => true, 'message' => __('Sorry, there was an unknown error.'));
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result));
    }

    /**
     * Delete folder action
     */
    public function deleteFolderAction()
    {
        try {
            $path = $this->_getSession()->getStoragePath();
            $this->_getStorage()->deleteDirectory($path);
        } catch (\Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result));
        }
    }

    /**
     * Contents action
     */
    public function contentsAction()
    {
        try {
            $this->_view->loadLayout('empty');
            $this->_view->getLayout()->getBlock('wysiwyg_files.files')->setStorage($this->_getStorage());
            $this->_view->renderLayout();

            $this->_getSession()->setStoragePath(
                $this->_objectManager->get('Magento\Theme\Helper\Storage')->getCurrentPath()
            );
        } catch (\Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result));
        }
    }

    /**
     * Files upload action
     */
    public function uploadAction()
    {
        try {
            $path = $this->_getSession()->getStoragePath();
            $result = $this->_getStorage()->uploadFile($path);
        } catch (\Exception $e) {
            $result = array('error' => $e->getMessage(), 'errorcode' => $e->getCode());
        }
        $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result));
    }

    /**
     * Preview image action
     */
    public function previewImageAction()
    {
        $file = $this->getRequest()->getParam('file');
        /** @var $helper \Magento\Theme\Helper\Storage */
        $helper = $this->_objectManager->get('Magento\Theme\Helper\Storage');
        try {
            return $this->_fileFactory->create(
                $file,
                array(
                    'type'  => 'filename',
                    'value' => $helper->getThumbnailPath($file)
                ),
                \Magento\Filesystem::MEDIA
            );
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Logger')->logException($e);
            $this->_redirect('core/index/notfound');
        }
    }

    /**
     * Delete file from media storage
     * @throws \Exception
     */
    public function deleteFilesAction()
    {
        try {
            if (!$this->getRequest()->isPost()) {
                throw new \Exception('Wrong request');
            }
            $files = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonDecode(
                $this->getRequest()->getParam('files')
            );
            foreach ($files as $file) {
                $this->_getStorage()->deleteFile($file);
            }
        } catch (\Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result));
        }
    }

    /**
     * Fire when select image
     */
    public function onInsertAction()
    {
        /** @var $helperStorage \Magento\Theme\Helper\Storage */
        $helperStorage = $this->_objectManager->get('Magento\Theme\Helper\Storage');
        $this->getResponse()->setBody($helperStorage->getRelativeUrl());
    }

    /**
     * Get storage
     *
     * @return \Magento\Theme\Model\Wysiwyg\Storage
     */
    protected function _getStorage()
    {
        return $this->_objectManager->get('Magento\Theme\Model\Wysiwyg\Storage');
    }
}
