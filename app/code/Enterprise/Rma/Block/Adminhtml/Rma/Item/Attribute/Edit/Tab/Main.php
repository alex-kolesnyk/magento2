<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Rma
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * RMA Item Attributes Edit Form
 *
 * @category    Enterprise
 * @package     Enterprise_Rma
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Block_Adminhtml_Rma_Item_Attribute_Edit_Tab_Main
    extends Magento_Eav_Block_Adminhtml_Attribute_Edit_Main_Abstract
    implements Magento_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Rma eav
     *
     * @var Enterprise_Rma_Helper_Eav
     */
    protected $_rmaEav = null;

    /**
     * @param Enterprise_Rma_Helper_Eav $rmaEav
     * @param Magento_Eav_Helper_Data $eavData
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Backend_Block_Template_Context $context
     * @param array $data
     */
    public function __construct(
        Enterprise_Rma_Helper_Eav $rmaEav,
        Magento_Eav_Helper_Data $eavData,
        Magento_Core_Helper_Data $coreData,
        Magento_Backend_Block_Template_Context $context,
        array $data = array()
    ) {
        $this->_rmaEav = $rmaEav;
        parent::__construct($eavData, $coreData, $context, $data);
    }

    /**
     * Preparing global layout
     *
     * @return Magento_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $result = parent::_prepareLayout();
        $renderer = $this->getLayout()->getBlock('fieldset_element_renderer');
        if ($renderer instanceof Magento_Data_Form_Element_Renderer_Interface) {
            Magento_Data_Form::setFieldsetElementRenderer($renderer);
        }
        return $result;
    }

    /**
     * Adding customer form elements for edit form
     *
     * @return Enterprise_Rma_Block_Adminhtml_Rma_Item_Attribute_Edit_Tab_Main
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $attribute  = $this->getAttributeObject();
        $form       = $this->getForm();
        $fieldset   = $form->getElement('base_fieldset');
        /* @var $helper Enterprise_Rma_Helper_Eav */
        $helper     = $this->_rmaEav;

        $fieldset->removeField('frontend_class');
        $fieldset->removeField('is_unique');

        // update Input Types
        $element    = $form->getElement('frontend_input');
        $element->setValues($helper->getFrontendInputOptions());
        $element->setLabel(__('Input Type'));
        $element->setRequired(true);

        // add limitation to attribute code
        // customer attribute code can have prefix "rma_item_" and its length must be max length minus prefix length
        $element      = $form->getElement('attribute_code');
        $element->setNote(
            __('For internal use. Must be unique with no spaces. Maximum length of attribute code must be less than %1 symbols', Magento_Eav_Model_Entity_Attribute::ATTRIBUTE_CODE_MAX_LENGTH)
        );

        $fieldset->addField('multiline_count', 'text', array(
            'name'      => 'multiline_count',
            'label'     => __('Lines Count'),
            'title'     => __('Lines Count'),
            'required'  => true,
            'class'     => 'validate-digits-range digits-range-2-20',
            'note'      => __('Valid range 2-20')
        ), 'frontend_input');

        $fieldset->addField('input_validation', 'select', array(
            'name'      => 'input_validation',
            'label'     => __('Input Validation'),
            'title'     => __('Input Validation'),
            'values'    => array('' => __('None'))
        ), 'default_value_textarea');

        $fieldset->addField('min_text_length', 'text', array(
            'name'      => 'min_text_length',
            'label'     => __('Minimum Text Length'),
            'title'     => __('Minimum Text Length'),
            'class'     => 'validate-digits',
        ), 'input_validation');

        $fieldset->addField('max_text_length', 'text', array(
            'name'      => 'max_text_length',
            'label'     => __('Maximum Text Length'),
            'title'     => __('Maximum Text Length'),
            'class'     => 'validate-digits',
        ), 'min_text_length');

        $fieldset->addField('max_file_size', 'text', array(
            'name'      => 'max_file_size',
            'label'     => __('Maximum File Size (bytes)'),
            'title'     => __('Maximum File Size (bytes)'),
            'class'     => 'validate-digits',
        ), 'max_text_length');

        $fieldset->addField('file_extensions', 'text', array(
            'name'      => 'file_extensions',
            'label'     => __('File Extensions'),
            'title'     => __('File Extensions'),
            'note'      => __('Comma separated'),
        ), 'max_file_size');

        $fieldset->addField('max_image_width', 'text', array(
            'name'      => 'max_image_width',
            'label'     => __('Maximum Image Width (px)'),
            'title'     => __('Maximum Image Width (px)'),
            'class'     => 'validate-digits',
        ), 'file_extensions');

        $fieldset->addField('max_image_heght', 'text', array(
            'name'      => 'max_image_heght',
            'label'     => __('Maximum Image Height (px)'),
            'title'     => __('Maximum Image Height (px)'),
            'class'     => 'validate-digits',
        ), 'max_image_width');

        $fieldset->addField('input_filter', 'select', array(
            'name'      => 'input_filter',
            'label'     => __('Input/Output Filter'),
            'title'     => __('Input/Output Filter'),
            'values'    => array('' => __('None')),
        ));

        $yesnoSource = Mage::getModel('Magento_Backend_Model_Config_Source_Yesno')->toOptionArray();

        $fieldset = $form->addFieldset('front_fieldset', array(
            'legend'    => __('Frontend Properties')
        ));

        $fieldset->addField('is_visible', 'select', array(
            'name'      => 'is_visible',
            'label'     => __('Show on Frontend'),
            'title'     => __('Show on Frontend'),
            'values'    => $yesnoSource,
        ));

        $fieldset->addField('sort_order', 'text', array(
            'name'      => 'sort_order',
            'label'     => __('Sort Order'),
            'title'     => __('Sort Order'),
            'required'  => true,
            'class'     => 'validate-digits'
        ));

        $fieldset->addField('used_in_forms', 'multiselect', array(
            'name'         => 'used_in_forms',
            'label'        => __('Forms to Use In'),
            'title'        => __('Forms to Use In'),
            'values'       => $helper->getAttributeFormOptions(),
            'value'        => $attribute->getUsedInForms(),
            'can_be_empty' => true,
        ))->setSize(5);

        if ($attribute->getId()) {
            $elements = array();
            if ($attribute->getIsSystem()) {
                $elements = array('sort_order', 'is_visible', 'is_required');
            }
            if (!$attribute->getIsUserDefined() && !$attribute->getIsSystem()) {
                $elements = array('sort_order');
            }
            foreach ($elements as $elementId) {
                $form->getElement($elementId)->setDisabled(true);
            }

            $inputTypeProp = $helper->getAttributeInputTypes($attribute->getFrontendInput());

            // input_filter
            if ($inputTypeProp['filter_types']) {
                $filterTypes = $helper->getAttributeFilterTypes();
                $values = $form->getElement('input_filter')->getValues();
                foreach ($inputTypeProp['filter_types'] as $filterTypeCode) {
                    $values[$filterTypeCode] = $filterTypes[$filterTypeCode];
                }
                $form->getElement('input_filter')->setValues($values);
            }

            // input_validation getAttributeValidateFilters
            if ($inputTypeProp['validate_filters']) {
                $filterTypes = $helper->getAttributeValidateFilters();
                $values = $form->getElement('input_validation')->getValues();
                foreach ($inputTypeProp['validate_filters'] as $filterTypeCode) {
                    $values[$filterTypeCode] = $filterTypes[$filterTypeCode];
                }
                $form->getElement('input_validation')->setValues($values);
            }
        }

        // apply scopes
        foreach ($helper->getAttributeElementScopes() as $elementId => $scope) {
            $element = $form->getElement($elementId);
            if ($element) {
                $element->setScope($scope);
                if ($this->getAttributeObject()->getWebsite()->getId()) {
                    $element->setName('scope_' . $element->getName());
                }
            }
        }

        $this->getForm()->setDataObject($this->getAttributeObject());

        return $this;
    }

    /**
     * Initialize form fileds values
     *
     * @return Magento_Eav_Block_Adminhtml_Attribute_Edit_Main_Abstract
     */
    protected function _initFormValues()
    {
        $attribute = $this->getAttributeObject();
        if ($attribute->getId() && $attribute->getValidateRules()) {
            $this->getForm()->addValues($attribute->getValidateRules());
        }
        $result = parent::_initFormValues();

        // get data using methods to apply scope
        $formValues = $this->getAttributeObject()->getData();
        foreach (array_keys($formValues) as $idx) {
            $formValues[$idx] = $this->getAttributeObject()->getDataUsingMethod($idx);
        }
        $this->getForm()->addValues($formValues);

        return $result;
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Properties');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Properties');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
