<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_AdvancedCheckout
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Enterprise checkout index controller
 *
 * @category   Magento
 * @package    Magento_AdvancedCheckout
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\AdvancedCheckout\Controller;

class Sku extends \Magento\App\Action\Action
{
    /**
     * Check functionality is enabled and applicable to the Customer
     *
     * @param \Magento\App\RequestInterface $request
     * @return mixed
     */
    public function dispatch(\Magento\App\RequestInterface $request)
    {
        // guest redirected to "Login or Create an Account" page
        /** @var $customerSession \Magento\Customer\Model\Session */
        $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
        if (!$customerSession->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
            return parent::dispatch($request);
        }

        /** @var $helper \Magento\AdvancedCheckout\Helper\Data */
        $helper = $this->_objectManager->get('Magento\AdvancedCheckout\Helper\Data');
        if (!$helper->isSkuEnabled() || !$helper->isSkuApplied()) {
            $this->_redirect('customer/account');
        }
        parent::dispatch($request);
    }

    /**
     * View Order by SKU page in 'My Account' section
     *
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_layoutServices->getLayout()->initMessages('Magento\Customer\Model\Session');
        $headBlock = $this->_layoutServices->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle(__('Order by SKU'));
        }
        $this->renderLayout();
    }

    /**
     * Upload file Action
     *
     * @return void
     */
    public function uploadFileAction()
    {
        /** @var $helper \Magento\AdvancedCheckout\Helper\Data */
        $helper = $this->_objectManager->get('Magento\AdvancedCheckout\Helper\Data');
        $rows = $helper->isSkuFileUploaded($this->getRequest())
            ? $helper->processSkuFileUploading($this->_getSession())
            : array();

        $items = $this->getRequest()->getPost('items');
        if (!is_array($items)) {
            $items = array();
        }
        foreach ($rows as $row) {
            $items[] = $row;
        }

        $this->getRequest()->setParam('items', $items);
        $this->_forward('advancedAdd', 'cart');
    }

    /**
     * Get checkout session model instance
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getSession()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }
}
