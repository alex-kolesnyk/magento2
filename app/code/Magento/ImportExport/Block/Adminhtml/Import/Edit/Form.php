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
 * Import edit form block
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ImportExport\Block\Adminhtml\Import\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Basic import model
     *
     * @var \Magento\ImportExport\Model\Import
     */
    protected $_importModel;

    /**
     * @var \Magento\ImportExport\Model\Source\Import\EntityFactory
     */
    protected $_entityFactory;

    /**
     * @var \Magento\ImportExport\Model\Source\Import\Behavior\Factory
     */
    protected $_behaviorFactory;

    /**
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Data\FormFactory $formFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\ImportExport\Model\Import $importModel
     * @param \Magento\ImportExport\Model\Source\Import\EntityFactory $entityFactory
     * @param \Magento\ImportExport\Model\Source\Import\Behavior\Factory $behaviorFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Registry $registry,
        \Magento\Data\FormFactory $formFactory,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\ImportExport\Model\Import $importModel,
        \Magento\ImportExport\Model\Source\Import\EntityFactory $entityFactory,
        \Magento\ImportExport\Model\Source\Import\Behavior\Factory $behaviorFactory,
        array $data = array()
    ) {
        $this->_entityFactory = $entityFactory;
        $this->_behaviorFactory = $behaviorFactory;
        parent::__construct($registry, $formFactory, $coreData, $context, $data);
        $this->_importModel = $importModel;
    }

    /**
     * Add fieldsets
     *
     * @return \Magento\ImportExport\Block\Adminhtml\Import\Edit\Form
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Data\Form $form */
        $form = $this->_formFactory->create(array(
            'attributes' => array(
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/*/validate'),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ))
        );

        // base fieldset
        $fieldsets['base'] = $form->addFieldset('base_fieldset', array('legend' => __('Import Settings')));
        $fieldsets['base']->addField('entity', 'select', array(
            'name'     => 'entity',
            'title'    => __('Entity Type'),
            'label'    => __('Entity Type'),
            'required' => true,
            'onchange' => 'varienImport.handleEntityTypeSelector();',
            'values'   => $this->_entityFactory->create()->toOptionArray(),
        ));

        // add behaviour fieldsets
        $uniqueBehaviors = $this->_importModel->getUniqueEntityBehaviors();
        foreach ($uniqueBehaviors as $behaviorCode => $behaviorClass) {
            $fieldsets[$behaviorCode] = $form->addFieldset(
                $behaviorCode . '_fieldset',
                array(
                    'legend' => __('Import Behavior'),
                    'class'  => 'no-display',
                )
            );
            /** @var $behaviorSource \Magento\ImportExport\Model\Source\Import\AbstractBehavior */
            $fieldsets[$behaviorCode]->addField($behaviorCode, 'select', array(
                'name'     => 'behavior',
                'title'    => __('Import Behavior'),
                'label'    => __('Import Behavior'),
                'required' => true,
                'disabled' => true,
                'values'   => $this->_behaviorFactory->create($behaviorClass)->toOptionArray(),
            ));
        }

        // fieldset for file uploading
        $fieldsets['upload'] = $form->addFieldset('upload_file_fieldset',
            array(
                'legend' => __('File to Import'),
                'class'  => 'no-display',
            )
        );
        $fieldsets['upload']->addField(\Magento\ImportExport\Model\Import::FIELD_NAME_SOURCE_FILE, 'file', array(
            'name'     => \Magento\ImportExport\Model\Import::FIELD_NAME_SOURCE_FILE,
            'label'    => __('Select File to Import'),
            'title'    => __('Select File to Import'),
            'required' => true,
            'class' => 'input-file'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
