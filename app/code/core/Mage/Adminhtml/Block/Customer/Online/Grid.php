<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Adminhtml customer grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_Online_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize Grid block
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('onlineGrid');
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('last_activity');
        $this->setDefaultDir('DESC');
    }

    /**
     * Prepare collection for grid
     *
     * @return Mage_Adminhtml_Block_Customer_Online_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Mage_Log_Model_Visitor_Online')
            ->prepare()
            ->getCollection();
        /* @var $collection Mage_Log_Model_Resource_Visitor_Online_Collection */
        $collection->addCustomerData();

        $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
    }

    /**
     * Prepare columns
     *
     * @return Mage_Adminhtml_Block_Customer_Online_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('customer_id', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('ID'),
            'width'     => '40px',
            'align'     => 'right',
            'type'      => 'number',
            'default'   => Mage::helper('Mage_Customer_Helper_Data')->__('n/a'),
            'index'     => 'customer_id'
        ));

        $this->addColumn('firstname', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('First Name'),
            'default'   => Mage::helper('Mage_Customer_Helper_Data')->__('Guest'),
            'index'     => 'customer_firstname'
        ));

        $this->addColumn('lastname', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Last Name'),
            'default'   => Mage::helper('Mage_Customer_Helper_Data')->__('n/a'),
            'index'     => 'customer_lastname'
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Email'),
            'default'   => Mage::helper('Mage_Customer_Helper_Data')->__('n/a'),
            'index'     => 'customer_email'
        ));

        $this->addColumn('ip_address', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('IP Address'),
            'default'   => Mage::helper('Mage_Customer_Helper_Data')->__('n/a'),
            'index'     => 'remote_addr',
            'renderer'  => 'Mage_Adminhtml_Block_Customer_Online_Grid_Renderer_Ip',
            'filter'    => false,
            'sort'      => false
        ));

        $this->addColumn('session_start_time', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Session Start Time'),
            'align'     => 'left',
            'width'     => '200px',
            'type'      => 'datetime',
            'default'   => Mage::helper('Mage_Customer_Helper_Data')->__('n/a'),
            'index'     =>'first_visit_at'
        ));

        $this->addColumn('last_activity', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Last Activity'),
            'align'     => 'left',
            'width'     => '200px',
            'type'      => 'datetime',
            'default'   => Mage::helper('Mage_Customer_Helper_Data')->__('n/a'),
            'index'     => 'last_visit_at'
        ));

        $typeOptions = array(
            Mage_Log_Model_Visitor::VISITOR_TYPE_CUSTOMER => Mage::helper('Mage_Customer_Helper_Data')->__('Customer'),
            Mage_Log_Model_Visitor::VISITOR_TYPE_VISITOR  => Mage::helper('Mage_Customer_Helper_Data')->__('Visitor'),
        );

        $this->addColumn('type', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Type'),
            'index'     => 'type',
            'type'      => 'options',
            'options'   => $typeOptions,
//            'renderer'  => 'Mage_Adminhtml_Block_Customer_Online_Grid_Renderer_Type',
            'index'     => 'visitor_type'
        ));

        $this->addColumn('last_url', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Last URL'),
            'type'      => 'wrapline',
            'lineLength' => '60',
            'default'   => Mage::helper('Mage_Customer_Helper_Data')->__('n/a'),
            'renderer'  => 'Mage_Adminhtml_Block_Customer_Online_Grid_Renderer_Url',
            'index'     => 'last_url'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve Row URL
     *
     * @param Mage_Core_Model_Abstract
     * @return string
     */
    public function getRowUrl($row)
    {
        return (Mage::getSingleton('Mage_Backend_Model_Auth_Session')->isAllowed('Mage_Customer::manage') && $row->getCustomerId())
            ? $this->getUrl('*/customer/edit', array('id' => $row->getCustomerId())) : '';
    }
}
