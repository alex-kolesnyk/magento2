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
 * Adminhtml tag edit form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Alexander Stadnitski <alexander@varien.com>
 * @author      Michael Bessolov <michael@varien.com>
 */

class Mage_Adminhtml_Block_System_Store_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('store_form');
        $this->setTitle(__('Store Information'));
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('admin_current_store');

        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'POST'));

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>__('General Information')));

        if ($model->getStoreId()) {
            $fieldset->addField('store_id', 'hidden', array(
                'name' => 'store_id',
            ));
        }

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => __('Store Name'),
            'title' => __('Store Name'),
            'required' => true,
        ));

        $fieldset->addField('code', 'text', array(
            'name' => 'code',
            'label' => __('Store Code'),
            'title' => __('Store Code'),
            'required' => true,
            'class' => 'validate-code',
        ));

        $fieldset->addField('is_active', 'select', array(
            'label' => __('Status'),
            'title' => __('Status'),
            'name' => 'is_active',
            'required' => true,
            'options' => array(
                0=>__('Disabled'),
                1=>__('Enabled'),
            ),
        ));
        
        $fieldset->addField('website_id', 'select', array(
            'label' => __('Website'),
            'title' => __('Website'),
            'name' => 'website_id',
            'required' => true,
            'values' => Mage::getResourceModel('core/website_collection')->load()->toOptionArray(),
        ));

        $fieldset->addField('sort_order', 'text', array(
            'name' => 'sort_order',
            'label' => __('Sort order'),
            'title' => __('Sort order'),
        ));
        $form->setValues($model->getData());

        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
