<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_GiftWrapping
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Gift wrapping adminhtml sales order view items
 *
 * @category   Magento
 * @package    Magento_GiftWrapping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GiftWrapping\Block\Adminhtml\Sales\Order\View;

class Link extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\GiftWrapping\Model\WrappingFactory
     */
    protected $_wrappingFactory;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\GiftWrapping\Model\WrappingFactory $wrappingFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\GiftWrapping\Model\WrappingFactory $wrappingFactory,
        array $data = array()
    ) {
        $this->_wrappingFactory = $wrappingFactory;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Get order item from parent block
     *
     * @return \Magento\Sales\Model\Order\Item
     */
    public function getItem()
    {
        return $this->getParentBlock()->getItem();
    }

    /**
     * Get gift wrapping design
     *
     * @return string
     */
    public function getDesign()
    {
        if ($this->getItem()->getGwId()) {
            $wrappingModel = $this->_wrappingFactory->create()->load($this->getItem()->getGwId());
            if ($wrappingModel->getId()) {
                return $this->escapeHtml($wrappingModel->getDesign());
            }
        }
        return '';
    }

    /**
     * Check ability to display gift wrapping for order items
     *
     * @return bool
     */
    public function canDisplayGiftWrappingForItem()
    {
        return $this->getItem()->getGwId() && $this->getDesign();
    }
}
