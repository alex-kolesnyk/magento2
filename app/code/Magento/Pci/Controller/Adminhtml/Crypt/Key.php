<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Pci
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Encryption key changer controller
 *
 */
namespace Magento\Pci\Controller\Adminhtml\Crypt;

class Key extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\App\Action\Title
     */
    protected $_title;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\App\Action\Title $title
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\App\Action\Title $title
    )
    {
        parent::__construct($context);
        $this->_title = $title;
    }

    /**
     * Check whether local.xml is writeable
     *
     * @return bool
     */
    protected function _checkIsLocalXmlWriteable()
    {
        $filename = $this->_objectManager->get('Magento\App\Dir')->getDir(\Magento\App\Dir::CONFIG)
            . DS . 'local.xml';
        if (!is_writeable($filename)) {
            $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError(
                __('To enable a key change this file must be writable: %1.', realpath($filename))
            );
            return false;
        }
        return true;
    }

    /**
     * Render main page with form
     *
     */
    public function indexAction()
    {
        $this->_title->add(__('Encryption Key'));

        $this->_checkIsLocalXmlWriteable();
        $this->_layoutServices->loadLayout();
        $this->_setActiveMenu('Magento_Pci::system_crypt_key');

        if (($formBlock = $this->_layoutServices->getLayout()->getBlock('pci.crypt.key.form'))
            && $data = $this->_objectManager->get('Magento\Adminhtml\Model\Session')->getFormData(true)) {
            /* @var \Magento\Pci\Block\Adminhtml\Crypt\Key\Form $formBlock */
            $formBlock->setFormData($data);
        }

        $this->_layoutServices->renderLayout();
    }

    /**
     * Process saving new encryption key
     *
     */
    public function saveAction()
    {
        try {
            $key = null;
            if (!$this->_checkIsLocalXmlWriteable()) {
                throw new \Exception('');
            }
            if (0 == $this->getRequest()->getPost('generate_random')) {
                $key = $this->getRequest()->getPost('crypt_key');
                if (empty($key)) {
                    throw new \Exception(__('Please enter an encryption key.'));
                }
                $this->_objectManager->get('Magento\Core\Helper\Data')->validateKey($key);
            }

            $newKey = $this->_objectManager->get('Magento\Pci\Model\Resource\Key\Change')
                ->changeEncryptionKey($key);
            $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                    ->addSuccess(
                __('The encryption key has been changed.')
            );

            if (!$key) {
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addNotice(
                    __('This is your new encryption key: <span style="font-family:monospace;">%1</span>. Be sure to write it down and take good care of it!', $newKey)
                );
            }
            $this->_objectManager->get('Magento\Core\Model\App')->cleanCache();
        }
        catch (\Exception $e) {
            if ($message = $e->getMessage()) {
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError($e->getMessage());
            }
            $this->_objectManager->get('Magento\Adminhtml\Model\Session')->setFormData(array('crypt_key' => $key));
        }
        $this->_redirect('adminhtml/*/');
    }

    /**
     * Check whether current administrator session allows this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Pci::crypt_key');
    }
}
