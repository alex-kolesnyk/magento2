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
 * Adminhtml newsletter templates grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Newsletter_Template_Grid extends Mage_Backend_Block_Widget_Grid_Extended
{
    /**
     * @param Mage_Backend_Block_Template_Context $context
     * @param Mage_Core_Model_StoreManagerInterface $storeManager
     * @param Mage_Core_Model_Url $urlModel
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Block_Template_Context $context,
        Mage_Core_Model_StoreManagerInterface $storeManager,
        Mage_Core_Model_Url $urlModel,
        array $data = array()
    ) {
        parent::__construct($context, $storeManager, $urlModel, $data);
        $this->setEmptyText(__('No Templates Found'));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceSingleton('Mage_Newsletter_Model_Resource_Template_Collection')
            ->useOnlyActual();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('template_code',
            array(
                'header'    => __('ID'),
                'index'     => 'template_id',
                'header_css_class'  => 'col-id',
                'column_css_class'  => 'col-id'
        ));
        $this->addColumn('code',
            array(
                'header'    => __('Template'),
                'index'     => 'template_code',
                'header_css_class'  => 'col-template',
                'column_css_class'  => 'col-template'
        ));

        $this->addColumn('added_at',
            array(
                'header'    => __('Added'),
                'index'     => 'added_at',
                'gmtoffset' => true,
                'type'      => 'datetime',
                'header_css_class'  => 'col-added',
                'column_css_class'  => 'col-added'
        ));

        $this->addColumn('modified_at',
            array(
                'header'    => __('Updated'),
                'index'     => 'modified_at',
                'gmtoffset' => true,
                'type'      => 'datetime',
                'header_css_class'  => 'col-updated',
                'column_css_class'  => 'col-updated'
        ));

        $this->addColumn('subject',
            array(
                'header'    => __('Subject'),
                'index'     => 'template_subject',
                'header_css_class'  => 'col-subject',
                'column_css_class'  => 'col-subject'
        ));

        $this->addColumn('sender',
            array(
                'header'    => __('Sender'),
                'index'     => 'template_sender_email',
                'renderer'  => 'Mage_Adminhtml_Block_Newsletter_Template_Grid_Renderer_Sender',
                'header_css_class'  => 'col-sender',
                'column_css_class'  => 'col-sender'
        ));

        $this->addColumn('type',
            array(
                'header'    => __('Template Type'),
                'index'     => 'template_type',
                'type'      => 'options',
                'options'   => array(
                    Mage_Newsletter_Model_Template::TYPE_HTML   => 'html',
                    Mage_Newsletter_Model_Template::TYPE_TEXT 	=> 'text'
                ),
                'header_css_class'  => 'col-type',
                'column_css_class'  => 'col-type'
        ));

        $this->addColumn('action',
            array(
                'header'    => __('Action'),
                'index'     => 'template_id',
                'sortable'  => false,
                'filter'    => false,
                'no_link'   => true,
                'renderer'  => 'Mage_Adminhtml_Block_Newsletter_Template_Grid_Renderer_Action',
                'header_css_class'  => 'col-actions',
                'column_css_class'  => 'col-actions'
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
    }

}

