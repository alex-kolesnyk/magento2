<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Newsletter
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Newsletter subscribers controller
 *
 * @category   Magento
 * @package    Magento_Newsletter
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Controller\Adminhtml;

class Problem extends \Magento\Backend\Controller\Adminhtml\Action
{
    public function indexAction()
    {
        $this->_title(__('Newsletter Problems Report'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->getLayout()->getMessagesBlock()->setMessages(
            $this->_objectManager->get('Magento\Adminhtml\Model\Session')->getMessages(true)
        );
        $this->loadLayout();

        $this->_setActiveMenu('Magento_Newsletter::newsletter_problem');

        $this->_addBreadcrumb(__('Newsletter Problem Reports'), __('Newsletter Problem Reports'));

        $this->renderLayout();
    }

    public function gridAction()
    {
        if($this->getRequest()->getParam('_unsubscribe')) {
            $problems = (array) $this->getRequest()->getParam('problem', array());
            if (count($problems)>0) {
                $collection = $this->_objectManager->create('Magento\Newsletter\Model\Resource\Problem\Collection');
                $collection
                    ->addSubscriberInfo()
                    ->addFieldToFilter($collection->getResource()->getIdFieldName(),
                                       array('in'=>$problems))
                    ->load();

                $collection->walk('unsubscribe');
            }

            $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                ->addSuccess(__('We unsubscribed the people you identified.'));
        }

        if($this->getRequest()->getParam('_delete')) {
            $problems = (array) $this->getRequest()->getParam('problem', array());
            if (count($problems)>0) {
                $collection = $this->_objectManager->create('Magento\Newsletter\Model\Resource\Problem\Collection');
                $collection
                    ->addFieldToFilter($collection->getResource()->getIdFieldName(),
                                       array('in'=>$problems))
                    ->load();
                $collection->walk('delete');
            }

            $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                ->addSuccess(__('The problems you identified have been deleted.'));
        }
                $this->getLayout()->getMessagesBlock()->setMessages($this->_objectManager->get('Magento\Adminhtml\Model\Session')->getMessages(true));

        $this->loadLayout(false);
        $this->renderLayout();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Newsletter::problem');
    }
}
