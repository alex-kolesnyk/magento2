<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Oauth
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Manage authorized tokens controller
 *
 * @category    Magento
 * @package     Magento_Oauth
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Oauth\Controller\Adminhtml\Oauth;

class AuthorizedTokens extends \Magento\Adminhtml\Controller\Action
{
    /**
     * Init titles
     *
     * @return \Magento\Oauth\Controller\Adminhtml\Oauth\AuthorizedTokens
     */
    public function preDispatch()
    {
        $this ->_title(__('Authorized Tokens'));
        parent::preDispatch();
        return $this;
    }

    /**
     * Render grid page
     */
    public function indexAction()
    {
        // TODO: Fix during Web API authentication implementation
        // $this->loadLayout()->_setActiveMenu('Magento_Oauth::system_legacy_api_oauth_authorized_tokens');
        // $this->renderLayout();
    }

    /**
     * Render grid AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Update revoke status action
     */
    public function revokeAction()
    {
        $ids = $this->getRequest()->getParam('items');
        $status = $this->getRequest()->getParam('status');

        if (!is_array($ids) || !$ids) {
            // No rows selected
            $this->_getSession()->addError(__('Please select needed row(s).'));
            $this->_redirect('*/*/index');
            return;
        }

        if (null === $status) {
            // No status selected
            $this->_getSession()->addError(__('Please select revoke status.'));
            $this->_redirect('*/*/index');
            return;
        }

        try {
            /** @var $collection \Magento\Oauth\Model\Resource\Token\Collection */
            $collection = \Mage::getModel('Magento\Oauth\Model\Token')->getCollection();
            $collection->joinConsumerAsApplication()
                    ->addFilterByType(\Magento\Oauth\Model\Token::TYPE_ACCESS)
                    ->addFilterById($ids)
                    ->addFilterByRevoked(!$status);

            /** @var $item \Magento\Oauth\Model\Token */
            foreach ($collection as $item) {
                $item->load($item->getId());
                $item->setRevoked($status)->save();

                $this->_sendTokenStatusChangeNotification($item, $status ? __('revoked') : __('enabled'));
            }
            if ($status) {
                $message = __('Selected entries revoked.');
            } else {
                $message = __('Selected entries enabled.');
            }
            $this->_getSession()->addSuccess($message);
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addError(__('An error occurred on update revoke status.'));
            $this->_objectManager->get('Magento_Core_Model_Logger')->logException($e);
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        $ids = $this->getRequest()->getParam('items');

        if (!is_array($ids) || !$ids) {
            // No rows selected
            $this->_getSession()->addError(__('Please select needed row(s).'));
            $this->_redirect('*/*/index');
            return;
        }

        try {
            /** @var $collection \Magento\Oauth\Model\Resource\Token\Collection */
            $collection = \Mage::getModel('Magento\Oauth\Model\Token')->getCollection();
            $collection->joinConsumerAsApplication()
                    ->addFilterByType(\Magento\Oauth\Model\Token::TYPE_ACCESS)
                    ->addFilterById($ids);

            /** @var $item \Magento\Oauth\Model\Token */
            foreach ($collection as $item) {
                $item->delete();

                $this->_sendTokenStatusChangeNotification($item, __('deleted'));
            }
            $this->_getSession()->addSuccess(__('Selected entries has been deleted.'));
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addError(__('An error occurred on delete action.'));
            $this->_objectManager->get('Magento_Core_Model_Logger')->logException($e);
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Oauth::authorizedTokens');
    }

    /**
     * Send email notification to user about token status change
     *
     * @param \Magento\Oauth\Model\Token $token Token object
     * @param string $newStatus Name of new token status
     */
    protected function _sendTokenStatusChangeNotification($token, $newStatus)
    {
        if (($adminId = $token->getAdminId())) {
            /** @var $session \Magento\Backend\Model\Auth\Session */
            $session = \Mage::getSingleton('Magento\Backend\Model\Auth\Session');

            /** @var $admin \Magento\User\Model\User */
            $admin = $session->getUser();

            if ($admin->getId() == $adminId) { // skip own tokens
                return;
            }
            $email = $admin->getEmail();
            $name  = $admin->getName(' ');
        } else {
            /** @var $customer \Magento\Customer\Model\Customer */
            $customer = \Mage::getModel('Magento\Customer\Model\Customer');

            $customer->load($token->getCustomerId());

            $email = $customer->getEmail();
            $name  = $customer->getName();
        }
        /** @var $oauthData \Magento\Oauth\Helper\Data */
        $oauthData = $this->_objectManager->get('Magento\Oauth\Helper\Data');
        $oauthData->sendNotificationOnTokenStatusChange($email, $name, $token->getConsumer()->getName(), $newStatus);
    }
}
