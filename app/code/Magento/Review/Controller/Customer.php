<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Review
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Customer reviews controller
 *
 * @category    Magento
 * @package     Magento_Review
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\Review\Controller;

use Magento\App\Action\NotFoundException;
use Magento\App\RequestInterface;

class Customer extends \Magento\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Check customer authentication for some actions
     *
     * @param RequestInterface $request
     * @return mixed
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->_customerSession->authenticate($this)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_layoutServices->getLayout()->initMessages('Magento\Catalog\Model\Session');

        if ($navigationBlock = $this->_layoutServices->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('review/customer');
        }
        if ($block = $this->_layoutServices->getLayout()->getBlock('review_customer_list')) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }

        $this->_layoutServices->getLayout()->getBlock('head')->setTitle(__('My Product Reviews'));

        $this->renderLayout();
    }

    public function viewAction()
    {
        $this->loadLayout();
        if ($navigationBlock = $this->_layoutServices->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('review/customer');
        }
        $this->_layoutServices->getLayout()->getBlock('head')->setTitle(__('Review Details'));
        $this->renderLayout();
    }
}
