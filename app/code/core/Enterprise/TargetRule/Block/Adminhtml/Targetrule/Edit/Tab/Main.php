<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Enterprise
 * @package    Enterprise_TargetRule
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Main target rules properties edit form
 *
 * @category   Enterprise
 * @package    Enterprise_TargetRule
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_TargetRule_Block_Adminhtml_Targetrule_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('current_target_rule');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('enterprise_targetrule')->__('General Information')));

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', array(
                'name' => 'rule_id',
            ));
        }

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => Mage::helper('enterprise_targetrule')->__('Rule Name'),
            'title' => Mage::helper('enterprise_targetrule')->__('Rule Name'),
            'required' => true,
        ));

        $fieldset->addField('sort_order', 'text', array(
            'name' => 'sort_order',
            'label' => Mage::helper('enterprise_targetrule')->__('Priority'),
        ));

        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('enterprise_targetrule')->__('Status'),
            'title'     => Mage::helper('enterprise_targetrule')->__('Status'),
            'name'      => 'is_active',
            'required'  => true,
            'options'   => array(
                '0' => Mage::helper('enterprise_targetrule')->__('Inactive'),
                '1' => Mage::helper('enterprise_targetrule')->__('Active'),
            ),
        ));

        $fieldset->addField('apply_to', 'select', array(
            'label'     => Mage::helper('enterprise_targetrule')->__('Apply To'),
            'title'     => Mage::helper('enterprise_targetrule')->__('Apply To'),
            'name'      => 'apply_to',
            'required'  => true,
            'options'   => Mage::getSingleton('enterprise_targetrule/rule')->getAppliesToOptions(),
        ));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('from_date', 'date', array(
            'name'         => 'from_date',
            'label'        => Mage::helper('enterprise_targetrule')->__('From Date'),
            'title'        => Mage::helper('enterprise_targetrule')->__('From Date'),
            'image'        => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));
        $fieldset->addField('to_date', 'date', array(
            'name'         => 'to_date',
            'label'        => Mage::helper('enterprise_targetrule')->__('To Date'),
            'title'        => Mage::helper('enterprise_targetrule')->__('To Date'),
            'image'        => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));

        $fieldset->addField('positions_limit', 'text', array(
            'name' => 'positions_limit',
            'label' => Mage::helper('enterprise_targetrule')->__('Positions Limit'),
        ));

        $form->setValues($model->getData());

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
