<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Downloadable
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Manage authorized tokens controller
 *
 * @category    Mage
 * @package     Mage_Oauth
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Oauth_Adminhtml_Oauth_AuthorizedTokensController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init titles
     *
     * @return Mage_Oauth_Adminhtml_Oauth_AuthorizedTokensController
     */
    public function preDispatch()
    {
        $this->_title($this->__('System'))
                ->_title($this->__('Oauth'))
                ->_title($this->__('Authorized Tokens'));
        parent::preDispatch();
        return $this;
    }

    /**
     * Render grid page
     */
    public function indexAction()
    {
        $this->loadLayout()->_setActiveMenu('system/oauth');
        $this->renderLayout();
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
            $this->_getSession()->addError($this->__('Please select needed row(s).'));
            $this->_redirect('*/*/index');
            return;
        }

        if (null === $status) {
            // No status selected
            $this->_getSession()->addError($this->__('Please select revoke status.'));
            $this->_redirect('*/*/index');
            return;
        }

        try {
            /** @var $collection Mage_Oauth_Model_Resource_Token_Collection */
            $collection = Mage::getModel('Mage_Oauth_Model_Token')->getCollection();
            $collection->joinConsumerAsApplication()
                    ->addFilterByType(Mage_Oauth_Model_Token::TYPE_ACCESS)
                    ->addFilterById($ids)
                    ->addFilterByRevoked(!$status);

            /** @var $item Mage_Oauth_Model_Token */
            foreach ($collection as $item) {
                $item->load($item->getId());
                $item->setRevoked($status)->save();

                $this->_sendTokenStatusChangeNotification($item, $status ? $this->__('revoked') : $this->__('enabled'));
            }
            if ($status) {
                $message = $this->__('Selected entries revoked.');
            } else {
                $message = $this->__('Selected entries enabled.');
            }
            $this->_getSession()->addSuccess($message);
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('An error occurred on update revoke status.'));
            Mage::logException($e);
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
            $this->_getSession()->addError($this->__('Please select needed row(s).'));
            $this->_redirect('*/*/index');
            return;
        }

        try {
            /** @var $collection Mage_Oauth_Model_Resource_Token_Collection */
            $collection = Mage::getModel('Mage_Oauth_Model_Token')->getCollection();
            $collection->joinConsumerAsApplication()
                    ->addFilterByType(Mage_Oauth_Model_Token::TYPE_ACCESS)
                    ->addFilterById($ids);

            /** @var $item Mage_Oauth_Model_Token */
            foreach ($collection as $item) {
                $item->delete();

                $this->_sendTokenStatusChangeNotification($item, $this->__('deleted'));
            }
            $this->_getSession()->addSuccess($this->__('Selected entries has been deleted.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('An error occurred on delete action.'));
            Mage::logException($e);
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
        /** @var $session Mage_Admin_Model_Session */
        $session = Mage::getSingleton('Mage_Admin_Model_Session');
        return $session->isAllowed('system/oauth/authorizedTokens');
    }

    /**
     * Send email notification to user about token status change
     *
     * @param Mage_Oauth_Model_Token $token Token object
     * @param string $newStatus Name of new token status
     */
    protected function _sendTokenStatusChangeNotification($token, $newStatus)
    {
        if (($adminId = $token->getAdminId())) {
            /** @var $session Mage_Admin_Model_Session */
            $session = Mage::getSingleton('Mage_Admin_Model_Session');

            /** @var $admin Mage_Admin_Model_User */
            $admin = $session->getUser();

            if ($admin->getId() == $adminId) { // skip own tokens
                return;
            }
            $email = $admin->getEmail();
            $name  = $admin->getName(' ');
        } else {
            /** @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getModel('Mage_Customer_Model_Customer');

            $customer->load($token->getCustomerId());

            $email = $customer->getEmail();
            $name  = $customer->getName();
        }
        /** @var $helper Mage_Oauth_Helper_Data */
        $helper = Mage::helper('Mage_Oauth_Helper_Data');

        $helper->sendNotificationOnTokenStatusChange($email, $name, $token->getConsumer()->getName(), $newStatus);
    }
}
