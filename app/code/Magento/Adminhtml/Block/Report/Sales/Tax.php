<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Adminhtml tax report page content block
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\Adminhtml\Block\Report\Sales;

class Tax extends \Magento\Adminhtml\Block\Widget\Grid\Container
{

    protected $_template = 'report/grid/container.phtml';

    protected function _construct()
    {
        $this->_controller = 'report_sales_tax';
        $this->_headerText = __('Order Taxes Report Grouped by Tax Rate');
        parent::_construct();

        $this->_removeButton('add');
        $this->addButton('filter_form_submit', array(
            'label'     => __('Show Report'),
            'onclick'   => 'filterFormSubmit()',
            'class'     => 'primary'
        ));
    }

    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/tax', array('_current' => true));
    }

}
