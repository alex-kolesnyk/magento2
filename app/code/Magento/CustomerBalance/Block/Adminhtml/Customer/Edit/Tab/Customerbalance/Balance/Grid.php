<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\CustomerBalance\Block\Adminhtml\Customer\Edit\Tab\Customerbalance\Balance;

class Grid extends
    \Magento\Backend\Block\Widget\Grid
{
    /**
     * @var \Magento\CustomerBalance\Model\BalanceFactory
     */
    protected $_balanceFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Model\Url $urlModel
     * @param \Magento\CustomerBalance\Model\BalanceFactory $balanceFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Model\Url $urlModel,
        \Magento\CustomerBalance\Model\BalanceFactory $balanceFactory,
        array $data = array()
    ) {
        $this->_balanceFactory = $balanceFactory;
        parent::__construct($context, $coreData, $urlModel, $data);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->_balanceFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', $this->getRequest()->getParam('id'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
}
