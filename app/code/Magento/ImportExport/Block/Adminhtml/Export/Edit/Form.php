<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Export edit form block
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ImportExport\Block\Adminhtml\Export\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\ImportExport\Model\Source\Export\EntityFactory
     */
    protected $_entityFactory;

    /**
     * @var \Magento\ImportExport\Model\Source\Export\FormatFactory
     */
    protected $_formatFactory;

    /**
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Data\FormFactory $formFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\ImportExport\Model\Source\Export\EntityFactory $entityFactory
     * @param \Magento\ImportExport\Model\Source\Export\FormatFactory $formatFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Registry $registry,
        \Magento\Data\FormFactory $formFactory,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\ImportExport\Model\Source\Export\EntityFactory $entityFactory,
        \Magento\ImportExport\Model\Source\Export\FormatFactory $formatFactory,
        array $data = array()
    ) {
        $this->_entityFactory = $entityFactory;
        $this->_formatFactory = $formatFactory;
        parent::__construct($registry, $formFactory, $coreData, $context, $data);
    }

    /**
     * Prepare form before rendering HTML.
     *
     * @return \Magento\ImportExport\Block\Adminhtml\Export\Edit\Form
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Data\Form $form */
        $form = $this->_formFactory->create(array(
            'attributes' => array(
                'id'     => 'edit_form',
                'action' => $this->getUrl('*/*/getFilter'),
                'method' => 'post',
            ))
        );

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Export Settings')));
        $fieldset->addField('entity', 'select', array(
            'name'     => 'entity',
            'title'    => __('Entity Type'),
            'label'    => __('Entity Type'),
            'required' => false,
            'onchange' => 'varienExport.getFilter();',
            'values'   => $this->_entityFactory->create()->toOptionArray()
        ));
        $fieldset->addField('file_format', 'select', array(
            'name'     => 'file_format',
            'title'    => __('Export File Format'),
            'label'    => __('Export File Format'),
            'required' => false,
            'values'   => $this->_formatFactory->create()->toOptionArray()
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
