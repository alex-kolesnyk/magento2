<?php
/**
 * description
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @subpackage  Promo_Catalog
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Moshe Gurvich <moshe@varien.com>
 */
class Mage_Adminhtml_Block_Promo_Catalog_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('current_promo_catalog_rule');

        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'POST'));

        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>__('General Information')));

        if ($model->getId()) {
        	$fieldset->addField('rule_id', 'hidden', array(
                'name' => 'rule_id',
            ));
        }

    	$fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => __('Rule Name'),
            'title' => __('Rule Name'),
            'required' => true,
        ));

        $fieldset->addField('description', 'textarea', array(
            'name' => 'description',
            'label' => __('Description'),
            'title' => __('Description'),
            'style' => 'width: 520px; height: 300px;',
            'required' => true,
        ));
        
    	$fieldset->addField('from_date', 'date', array(
            'name' => 'from_date',
            'label' => __('From Date'),
            'title' => __('From Date'),
            'required' => true,
        ));
        
    	$fieldset->addField('to_date', 'date', array(
            'name' => 'from_date',
            'label' => __('From Date'),
            'title' => __('From Date'),
            'required' => true,
        ));

        $stores = Mage::getResourceModel('core/store_collection')->load()->toOptionArray();

    	$fieldset->addField('store_ids', 'multiselect', array(
            'name'      => 'store_ids',
            'label'     => __('Stores'),
            'title'     => __('Stores'),
            'required'  => true,
            'values'    => $stores,
        ));
        
        $stores = Mage::getResourceModel('core/store_collection')->load()->toOptionArray();

    	$fieldset->addField('store_ids', 'multiselect', array(
            'name'      => 'store_ids',
            'label'     => __('Stores'),
            'title'     => __('Stores'),
            'required'  => true,
            'values'    => $stores,
        ));

    	$fieldset->addField('is_active', 'select', array(
            'label'     => __('Status'),
            'title'     => __('Status'),
            'name'      => 'is_active',
            'required' => true,
            'options'    => array(
                '1' => __('Enabled'),
                '0' => __('Disabled'),
            ),
        ));

        $form->setValues($model->getData());

        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }
