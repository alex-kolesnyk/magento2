<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_GiftRegistry
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Enterprise_GiftRegistry_Block_Adminhtml_Customer_Edit_Sharing
    extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Magento_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getActionUrl(),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => $this->helper('Enterprise_GiftRegistry_Helper_Data')->__('Sharing Information'),
            'class'  => 'fieldset-wide'
        ));

        $fieldset->addField('emails', 'text', array(
            'label'    => $this->helper('Enterprise_GiftRegistry_Helper_Data')->__('Emails'),
            'required' => true,
            'class'    => 'validate-emails',
            'name'     => 'emails',
            'note'     => 'Enter list of emails, comma-separated.'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'select', array(
                'label'    => $this->helper('Enterprise_GiftRegistry_Helper_Data')->__('Send From'),
                'required' => true,
                'name'     => 'store_id',
                'values'   => Mage::getSingleton('Mage_Core_Model_System_Store')->getStoreValuesForForm()
            ));
        }

        $fieldset->addField('message', 'textarea', array(
            'label' => $this->helper('Enterprise_GiftRegistry_Helper_Data')->__('Message'),
            'name'  => 'message',
            'style' => 'height: 50px;',
            'after_element_html' => $this->getShareButton()
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        $form->setDataObject();

        return parent::_prepareForm();
    }

    /**
     * Return sharing form action url
     *
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getUrl('*/*/share', array('_current' => true));
    }

    /**
     * Create button
     *
     * @return string
     */
    public function getShareButton()
    {
        return $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Button')
            ->addData(array(
                'id'      => '',
                'label'   => Mage::helper('Enterprise_GiftRegistry_Helper_Data')->__('Share Gift Registry'),
                'type'    => 'submit'
            ))->toHtml();
    }
}
