<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Customer
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Customer\Controller\Adminhtml;

class Online extends \Magento\Backend\App\Action
{
    public function indexAction()
    {
        $this->_title->add(__('Customers Now Online'));

        if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_layoutServices->loadLayout();

        $this->_setActiveMenu('Magento_Customer::customer_online');

        $this->_addBreadcrumb(__('Customers'), __('Customers'));
        $this->_addBreadcrumb(__('Online Customers'), __('Online Customers'));

        $this->_layoutServices->renderLayout();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Customer::online');
    }
}
