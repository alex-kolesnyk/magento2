<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Sendfriend
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Email to a Friend Product Controller
 *
 * @category    Magento
 * @package     Magento_Sedfriend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sendfriend\Controller;

class Product extends \Magento\Core\Controller\Front\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Core\Controller\Varien\Action\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Core\Controller\Varien\Action\Context $context,
        \Magento\Core\Model\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Predispatch: check is enable module
     * If allow only for customer - redirect to login page
     *
     * @return \Magento\Sendfriend\Controller\Product
     */
    public function preDispatch()
    {
        parent::preDispatch();

        /* @var $helper Magento_Sendfriend_Helper_Data */
        $helper = $this->_objectManager->get('Magento_Sendfriend_Helper_Data');
        /* @var $session Magento_Customer_Model_Session */
        $session = $this->_objectManager->get('Magento_Customer_Model_Session');

        if (!$helper->isEnabled()) {
            $this->norouteAction();
            return $this;
        }

        if (!$helper->isAllowForGuest() && !$session->authenticate($this)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            if ($this->getRequest()->getActionName() == 'sendemail') {
                $session->setBeforeAuthUrl($this->_objectManager
                        ->create('Magento_Core_Model_Url')
                        ->getUrl('*/*/send', array(
                            '_current' => true
                        )));
                $this->_objectManager->get('Magento_Catalog_Model_Session')
                    ->setSendfriendFormData($this->getRequest()->getPost());
            }
        }

        return $this;
    }

    /**
     * Initialize Product Instance
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function _initProduct()
    {
        $productId  = (int)$this->getRequest()->getParam('id');
        if (!$productId) {
            return false;
        }
        $product = $this->_objectManager->create('Magento_Catalog_Model_Product')
            ->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            return false;
        }

        $this->_coreRegistry->register('product', $product);
        return $product;
    }

    /**
     * Initialize send friend model
     *
     * @return \Magento\Sendfriend\Model\Sendfriend
     */
    protected function _initSendToFriendModel()
    {
        $model  = $this->_objectManager->create('Magento_Sendfriend_Model_Sendfriend');
        $model->setRemoteAddr($this->_objectManager->get('Magento_Core_Helper_Http')->getRemoteAddr(true));
        $model->setCookie($this->_objectManager->get('Magento_Core_Model_Cookie'));
        $model->setWebsiteId(
            $this->_objectManager
                ->get('Magento_Core_Model_StoreManagerInterface')
                ->getStore()
                ->getWebsiteId()
        );

        $this->_coreRegistry->register('send_to_friend_model', $model);

        return $model;
    }

    /**
     * Show Send to a Friend Form
     *
     */
    public function sendAction()
    {
        $product    = $this->_initProduct();
        $model      = $this->_initSendToFriendModel();

        if (!$product) {
            $this->_forward('noRoute');
            return;
        }
        /* @var $session Magento_Catalog_Model_Session */
        $catalogSession = $this->_objectManager->get('Magento_Catalog_Model_Session');

        if ($model->getMaxSendsToFriend() && $model->isExceedLimit()) {
            $catalogSession->addNotice(
                __('You can\'t send messages more than %1 times an hour.', $model->getMaxSendsToFriend())
            );
        }

        $this->loadLayout();
        $this->_initLayoutMessages('Magento\Catalog\Model\Session');

        $this->_eventManager->dispatch('sendfriend_product', array('product' => $product));
        $data = $catalogSession->getSendfriendFormData();
        if ($data) {
            $catalogSession->setSendfriendFormData(true);
            $block = $this->getLayout()->getBlock('sendfriend.send');
            if ($block) {
                $block->setFormData($data);
            }
        }

        $this->renderLayout();
    }

    /**
     * Send Email Post Action
     *
     */
    public function sendmailAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/send', array('_current' => true));
        }

        $product    = $this->_initProduct();
        $model      = $this->_initSendToFriendModel();
        $data       = $this->getRequest()->getPost();

        if (!$product || !$data) {
            $this->_forward('noRoute');
            return;
        }

        $categoryId = $this->getRequest()->getParam('cat_id', null);
        if ($categoryId) {
            $category = $this->_objectManager->create('Magento_Catalog_Model_Category')
                ->load($categoryId);
            $product->setCategory($category);
            $this->_coreRegistry->register('current_category', $category);
        }

        $model->setSender($this->getRequest()->getPost('sender'));
        $model->setRecipients($this->getRequest()->getPost('recipients'));
        $model->setProduct($product);

        /* @var $session Magento_Catalog_Model_Session */
        $catalogSession = $this->_objectManager->get('Magento_Catalog_Model_Session');
        try {
            $validate = $model->validate();
            if ($validate === true) {
                $model->send();
                $catalogSession->addSuccess(__('The link to a friend was sent.'));
                $this->_redirectSuccess($product->getProductUrl());
                return;
            }
            else {
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $catalogSession->addError($errorMessage);
                    }
                } else {
                    $catalogSession->addError(__('We found some problems with the data.'));
                }
            }
        } catch (Magento_Core_Exception $e) {
        catch (\Magento\Core\Exception $e) {
            $catalogSession->addError($e->getMessage());
        } catch (\Exception $e) {
            $catalogSession->addException($e, __('Some emails were not sent.'));
        }

        // save form data
        $catalogSession->setSendfriendFormData($data);

        $this->_redirectError(
            $this->_objectManager
                ->create('Magento_Core_Model_Url')
                ->getUrl('*/*/send', array('_current' => true))
        );
    }
}
