<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main;

class Formset
    extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $_setFactory;

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Data\Form\Factory $formFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        \Magento\Core\Model\Registry $registry,
        \Magento\Data\Form\Factory $formFactory,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->_setFactory = $setFactory;
        parent::__construct($registry, $formFactory, $coreData, $context, $data);
    }

    /**
     * Prepares attribute set form
     *
     */
    protected function _prepareForm()
    {
        $data = $this->_setFactory->create()->load($this->getRequest()->getParam('id'));

        /** @var \Magento\Data\Form $form */
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('set_name', array('legend'=> __('Edit Set Name')));
        $fieldset->addField('attribute_set_name', 'text', array(
            'label' => __('Name'),
            'note' => __('For internal use'),
            'name' => 'attribute_set_name',
            'required' => true,
            'class' => 'required-entry validate-no-html-tags',
            'value' => $data->getAttributeSetName()
        ));

        if( !$this->getRequest()->getParam('id', false) ) {
            $fieldset->addField('gotoEdit', 'hidden', array(
                'name' => 'gotoEdit',
                'value' => '1'
            ));

            $sets = $this->_setFactory->create()
                ->getResourceCollection()
                ->setEntityTypeFilter($this->_coreRegistry->registry('entityType'))
                ->load()
                ->toOptionArray();

            $fieldset->addField('skeleton_set', 'select', array(
                'label' => __('Based On'),
                'name' => 'skeleton_set',
                'required' => true,
                'class' => 'required-entry',
                'values' => $sets,
            ));
        }

        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('set-prop-form');
        $form->setAction($this->getUrl('catalog/*/save'));
        $form->setOnsubmit('return false;');
        $this->setForm($form);
    }
}
