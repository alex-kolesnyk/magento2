<?php
/**
 * Adminhtml sales orders grid
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Michael Bessolov <michael@varien.com>
 */

class Mage_Adminhtml_Block_Sales_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToSelect('*')
            ->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id')
            ->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id')
            ->joinAttribute('billing_telephone', 'order_address/telephone', 'billing_address_id')
            ->joinAttribute('billing_postcode', 'order_address/postcode', 'billing_address_id')
            ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id')
            ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id')
            ->joinAttribute('shipping_telephone', 'order_address/telephone', 'shipping_address_id')
            ->joinAttribute('shipping_postcode', 'order_address/postcode', 'shipping_address_id')
        ;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('real_order_id', array(
            'header' => __('Order #'),
            'align' => 'center',
            'index' => 'increment_id',
        ));

        $stores = Mage::getResourceModel('core/store_collection')->setWithoutDefaultFilter()->load()->toOptionHash();

        $this->addColumn('store_id', array(
            'header' => __('Purchased from (store)'),
            'index' => 'store_id',
            'type' => 'options',
            'options' => $stores,
        ));

        $this->addColumn('created_at', array(
            'header' => __('Purchased at'),
            'index' => 'created_at',
            'type' => 'datetime',
        ));

        $this->addColumn('billing_firstname', array(
            'header' => __('Bill to Firstname'),
            'index' => 'billing_firstname',
        ));

        $this->addColumn('billing_lastname', array(
            'header' => __('Bill to Lastname'),
            'index' => 'billing_lastname',
        ));

        $this->addColumn('shipping_firstname', array(
            'header' => __('Ship to Firstname'),
            'index' => 'shipping_firstname',
        ));

        $this->addColumn('shipping_lastname', array(
            'header' => __('Ship to Lastname'),
            'index' => 'shipping_lastname',
        ));

        $this->addColumn('grand_total', array(
            'header' => __('Grand Total'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        $statuses = Mage::getResourceModel('sales/order_status_collection')->load()->toOptionHash();

        $this->addColumn('status', array(
            'header' => __('Status'),
            'index' => 'order_status_id',
            'type'  => 'options',
            'options' => $statuses,
        ));

        $this->addColumn('actions', array(
            'header' => __('Action'),
            'width' => 10,
            'sortable' => false,
            'filter' => false,
            'type' => 'action',
            'actions' => array(
                array(
                    'url' => Mage::getUrl('*/*/edit') . 'order_id/$entity_id',
                    'caption' => __('Edit'),
                ),
            )
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return Mage::getUrl('*/*/view', array('order_id' => $row->getId()));
    }

}
