<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Adminhtml sales order's status management block
 *
 * @category    Magento
 * @package     Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Block\Adminhtml\Order;

class Status extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Class constructor
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_order_status';
        $this->_headerText = __('Order Statuses');
        $this->_addButtonLabel = __('Create New Status');
        $this->_addButton('assign', array(
            'label'     => __('Assign Status to State'),
            'onclick'   => 'setLocation(\'' . $this->getAssignUrl() .'\')',
            'class'     => 'add',
        ));
        parent::_construct();
    }

    /**
     * Create url getter
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('sales/order_status/new');
    }

    /**
     * Assign url getter
     *
     * @return string
     */
    public function getAssignUrl()
    {
        return $this->getUrl('sales/order_status/assign');
    }
}
