<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml abandoned shopping carts report grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Dmytro Vasylenko <dimav@varien.com>
 */
class Mage_Adminhtml_Block_Report_Shopcart_abandoned_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('gridAbandoned');
    }

    protected function _prepareCollection()
    {
        if ($this->getRequest()->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } else if ($this->getRequest()->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        } else if ($this->getRequest()->getParam('store')) {
            $storeIds = array((int)$this->getRequest()->getParam('store'));
        } else {
            $storeIds = '';
        }

        $collection = Mage::getResourceModel('reports/quote_collection')
            ->addAttributeToSelect('*')
            ->setActiveFilter()
            ->addCustomerName()
            ->addAttributeToSelect('coupon_code')
            ->addQuoteItems()
            ->addSubtotal($storeIds)
            ->groupByAttribute('entity_id')
            ->setOrder('updated_at');

        if (is_array($storeIds)) {
            $collection->addAttributeToFilter('store_id', array('in' => $storeIds));
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('customer_name', array(
            'header'    =>Mage::helper('reports')->__('Customer Name'),
            'index'     =>'customer_name',
            'sortable'  =>false,
            'filter'    =>false
        ));

        $this->addColumn('items', array(
            'header'    =>Mage::helper('reports')->__('Number of Items'),
            'width'     =>'80px',
            'align'     =>'right',
            'index'     =>'items',
            'sortable'  =>false,
            'filter'    =>false
        ));

        $this->addColumn('items_qty', array(
            'header'    =>Mage::helper('reports')->__('Quantity of Items'),
            'width'     =>'80px',
            'align'     =>'right',
            'index'     =>'items_qty',
            'sortable'  =>false,
            'filter'    =>false
        ));

        $this->addColumn('subtotal', array(
            'header'    =>Mage::helper('reports')->__('Subtotal'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'     =>'subtotal',
            'sortable'  =>false,
            'filter'    =>false
        ));

        $this->addColumn('coupon_code', array(
            'header'    =>Mage::helper('reports')->__('Applied Coupon'),
            'width'     =>'80px',
            'index'     =>'coupon_code',
            'sortable'  =>false,
            'filter'    =>false
        ));

        $this->addColumn('created_at', array(
            'header'    =>Mage::helper('reports')->__('Created at'),
            'width'     =>'170px',
            'type'      =>'datetime',
            'index'     =>'created_at',
            'sortable'  =>false,
            'filter'    =>false
        ));

        $this->addColumn('updated_at', array(
            'header'    =>Mage::helper('reports')->__('Updated at'),
            'width'     =>'170px',
            'type'      =>'datetime',
            'index'     =>'updated_at',
            'sortable'  =>false
        ));

        $this->addExportType('*/*/exportAbandonedCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportAbandonedExcel', Mage::helper('reports')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/customer/edit', array('id'=>$row->customer_id, 'active_tab'=>'cart'));
    }

    public function getRowClickCallback(){
        return "function(grid, evt) {
            var trElement = Event.findElement(evt, 'tr');
            console.log(trElement);
            if(trElement){
                var newWindow = window.open(trElement.id, '_blank');
                newWindow.focus();
            }}";
    }
}