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
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Catalog_Product_Attribute_Set_Main_Formattribute extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Magento_Data_Form();

        $fieldset = $form->addFieldset('set_fieldset', array('legend'=>Mage::helper('Mage_Catalog_Helper_Data')->__('Add New Attribute')));

        $fieldset->addField('new_attribute', 'text',
                            array(
                                'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Name'),
                                'name' => 'new_attribute',
                                'required' => true,
                            )
        );

        $fieldset->addField('submit', 'note',
                            array(
                                'text' => $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Button')
                                            ->setData(array(
                                                'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Add Attribute'),
                                                'onclick'   => 'this.form.submit();',
                                                                                                'class' => 'add'
                                            ))
                                            ->toHtml(),
                            )
        );

        $form->setUseContainer(true);
        $form->setMethod('post');
        $this->setForm($form);
    }
}
