<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Enterprise
 * @package    Enterprise_Staging
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Staging Manage Grid
 *
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Manage_Staging_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('enterpriseStagingManageGrid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        //$this->setTemplate('enterprise/staging/manage/grid.phtml');
    }

    /**
     * PrepareCollection method.
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('enterprise_staging/staging_collection');
        
        foreach($collection AS $datasetItem) {
            $collection->getItemById($datasetItem->getId())
                ->setData("lastEvent", $datasetItem->getEventsCollection()->getFirstItem()->getComment());
        }
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Configuration of grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    => 'Name',
            'index'     => 'name',
            'type'      => 'text',
        ));

        $this->addColumn('lastEvent', array(
            'width'     => '250px',        
            'header'    => 'Latest Event',
            'index'     => 'lastEvent',
            'type'      => 'text',
            'filter'    => false,
            'sortable'  => false,
        ));
        
        $this->addColumn('created_at', array(
            'width'     => '150px',
            'header'    => 'Created At',
            'index'     => 'created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('updated_at', array(
            'width'     => '150px',        
            'header'    => 'Updated At',
            'index'     => 'updated_at',
            'type'      => 'datetime',
        ));
        
        $actions = array();
        $actions[] = array(
            'caption' => Mage::helper('enterprise_staging')->__('Edit'),
            'url'     => array(
                'base'=>'*/*/edit',
                'params'=>array('store'=>$this->getRequest()->getParam('store'))
            ),
            'field'   => 'id'
        );

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('enterprise_staging')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => $actions,
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
        ));

        return $this;
    }
    
    /**
     * Return grids url
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * Return Row Url
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'id'    => $row->getId())
        );
    }
}