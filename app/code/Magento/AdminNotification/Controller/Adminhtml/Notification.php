<?php
/**
 * Adminhtml AdminNotification controller
 *
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_AdminNotification
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\AdminNotification\Controller\Adminhtml;

class Notification extends \Magento\Backend\Controller\AbstractAction
{
    public function indexAction()
    {
        $this->_title(__('Notifications'));

        $this->loadLayout()
            ->_setActiveMenu('Magento_AdminNotification::system_adminnotification')
            ->_addBreadcrumb(
                __('Messages Inbox'),
                __('Messages Inbox')
            )->renderLayout();
    }

    public function markAsReadAction()
    {
        $notificationId = (int)$this->getRequest()->getParam('id');
        if ($notificationId) {
            try {
                $this->_objectManager->create('Magento\AdminNotification\Model\NotificationService')
                    ->markAsRead($notificationId);
                $this->_session->addSuccess(
                    __('The message has been marked as Read.')
                );
            } catch (\Magento\Core\Exception $e) {
                $this->_session->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_session->addException($e,
                    __("We couldn't mark the notification as Read because of an error.")
                );
            }

            $this->_redirectReferer();
            return;
        }
        $this->_redirect('adminhtml/*/');
    }

    /**
     * Mark notification as read (AJAX action)
     */
    public function ajaxMarkAsReadAction()
    {
        if (!$this->getRequest()->getPost()) {
            return;
        }
        $notificationId = (int)$this->getRequest()->getPost('id');
        $responseData = array();
        try {
            $this->_objectManager->create('Magento\AdminNotification\Model\NotificationService')
                ->markAsRead($notificationId);
            $responseData['success'] = true;
        } catch (\Exception $e) {
            $responseData['success'] = false;
        }
        $this->getResponse()->setBody(
            $this->_objectManager->create('Magento\Core\Helper\Data')->jsonEncode($responseData)
        );
    }

    public function massMarkAsReadAction()
    {
        $ids = $this->getRequest()->getParam('notification');
        if (!is_array($ids)) {
            $this->_session->addError(__('Please select messages.'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = $this->_objectManager->create('Magento\AdminNotification\Model\Inbox')
                        ->load($id);
                    if ($model->getId()) {
                        $model->setIsRead(1)
                            ->save();
                    }
                }
                $this->_getSession()->addSuccess(
                    __('A total of %1 record(s) have been marked as Read.', count($ids))
                );
            } catch (\Magento\Core\Exception $e) {
                $this->_session->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_session->addException($e,
                    __("We couldn't mark the notification as Read because of an error.")
                );
            }
        }
        $this->_redirect('adminhtml/*/');
    }

    public function removeAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $model = $this->_objectManager->create('Magento\AdminNotification\Model\Inbox')
                ->load($id);

            if (!$model->getId()) {
                $this->_redirect('adminhtml/*/');
                return ;
            }

            try {
                $model->setIsRemove(1)
                    ->save();
                $this->_session->addSuccess(
                    __('The message has been removed.')
                );
            } catch (\Magento\Core\Exception $e) {
                $this->_session->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_session->addException($e,
                    __("We couldn't remove the messages because of an error.")
                );
            }

            $this->_redirect('adminhtml/*/');
            return;
        }
        $this->_redirect('adminhtml/*/');
    }

    public function massRemoveAction()
    {
        $ids = $this->getRequest()->getParam('notification');
        if (!is_array($ids)) {
            $this->_session->addError(
                __('Please select messages.')
            );
        } else {
            try {
                foreach ($ids as $id) {
                    $model = $this->_objectManager->create('Magento\AdminNotification\Model\Inbox')
                        ->load($id);
                    if ($model->getId()) {
                        $model->setIsRemove(1)
                            ->save();
                    }
                }
                $this->_getSession()->addSuccess(
                    __('Total of %1 record(s) have been removed.', count($ids))
                );
            } catch (\Magento\Core\Exception $e) {
                $this->_session->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_session->addException($e,
                    __("We couldn't remove the messages because of an error."));
            }
        }
        $this->_redirectReferer();
    }

    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'markAsRead':
                $acl = 'Magento_AdminNotification::mark_as_read';
                break;

            case 'massMarkAsRead':
                $acl = 'Magento_AdminNotification::mark_as_read';
                break;

            case 'remove':
                $acl = 'Magento_AdminNotification::adminnotification_remove';
                break;

            case 'massRemove':
                $acl = 'Magento_AdminNotification::adminnotification_remove';
                break;

            default:
                $acl = 'Magento_AdminNotification::show_list';
        }
        return $this->_authorization->isAllowed($acl);
    }
}
