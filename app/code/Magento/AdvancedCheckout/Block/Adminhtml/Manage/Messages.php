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
 * Admin Checkout block for showing messages
 *
 * @category    Magento
 * @package     Magento_AdvancedCheckout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\AdvancedCheckout\Block\Adminhtml\Manage;

class Messages extends \Magento\Adminhtml\Block\Messages
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\View\Block\Template\Context $context
     * @param \Magento\Message\Factory $messageFactory
     * @param \Magento\Message\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Core\Helper\Data $coreData,
        \Magento\View\Block\Template\Context $context,
        \Magento\Message\Factory $messageFactory,
        \Magento\Message\CollectionFactory $collectionFactory,
        array $data = array()
    ) {
        $this->backendSession = $backendSession;
        parent::__construct($coreData, $context, $messageFactory, $collectionFactory, $data);
    }

    /**
     * Prepares layout for current block
     */
    protected function _prepareLayout()
    {
        $this->addMessages($this->backendSession->getMessages(true));
        parent::_prepareLayout();
    }
}
