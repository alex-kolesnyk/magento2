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
 * @package    Enterprise_GiftCardAccount
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://www.magentocommerce.com/license/enterprise-edition
 */

class Enterprise_GiftCardAccount_Block_Adminhtml_Giftcardaccount_Edit_Tab_Send extends Mage_Adminhtml_Block_Widget_Form
{
    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_send');
        $form->setFieldNameSuffix('send');

        $model = Mage::registry('current_giftcardaccount');

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend'=>Mage::helper('enterprise_giftcardaccount')->__('Send Gift Card'))
        );

/*
        $emailTemplates = array();
        foreach (Mage::getModel('adminhtml/system_config_source_email_template')->toOptionArray() as $option) {
            $emailTemplates[$option['value']] = $option['label'];
        }

        $fieldset->addField('email_template', 'select', array(
            'label'     => Mage::helper('enterprise_giftcardaccount')->__('Email Template'),
            'title'     => Mage::helper('enterprise_giftcardaccount')->__('Email Template'),
            'name'      => 'email_template',
            'options'   => $emailTemplates,
        ));
*/

        $fieldset->addField('recipient_email', 'text', array(
            'label'     => Mage::helper('enterprise_giftcardaccount')->__('Recipient Email'),
            'title'     => Mage::helper('enterprise_giftcardaccount')->__('Recipient Email'),
            'class'     => 'validate-email',
            'name'      => 'recipient_email',
        ));

        $fieldset->addField('recipient_name', 'text', array(
            'label'     => Mage::helper('enterprise_giftcardaccount')->__('Recipient Name'),
            'title'     => Mage::helper('enterprise_giftcardaccount')->__('Recipient Name'),
            'name'      => 'recipient_name',
        ));

        $fieldset->addField('store_id', 'select', array(
            'name'     => 'store_id',
            'label'    => Mage::helper('enterprise_customerbalance')->__('Send email from the following Store View'),
            'title'    => Mage::helper('enterprise_customerbalance')->__('Send email from the following Store View'),
            'after_element_html'=>$this->_getStoreIdScript()
        ));

        $fieldset->addField('action', 'hidden', array(
            'name'      => 'action',
        ));

        $this->setForm($form);
        return $this;
    }

    protected function _getStoreIdScript()
    {
        $websiteStores = array();
        foreach (Mage::app()->getWebsites() as $websiteId => $website) {
            $websiteStores[$websiteId] = array();
            foreach ($website->getGroups() as $groupId => $group) {
                $websiteStores[$websiteId][$groupId] = array(
                    'name' => $group->getName()
                );
                foreach ($group->getStores() as $storeId => $store) {
                    $websiteStores[$websiteId][$groupId]['stores'][] = array(
                        'id'   => $storeId,    
                        'name' => $store->getName(),
                    );
                }
            }
        }
        
        $websiteStores = Zend_Json::encode($websiteStores);

        $result  = '<script type="text/javascript">//<![CDATA[' . "\n";
        $result .= "var websiteStores = $websiteStores;";
        $result .= "Event.observe('_infowebsite_id', 'change', setCurrentStores);";
        $result .= "setCurrentStores();";
        $result .= 'function setCurrentStores(){
            var wSel = $("_infowebsite_id");
            var sSel = $("_sendstore_id");

            sSel.innerHTML = "";
            var website = wSel.options[wSel.selectedIndex].value;
            if (websiteStores[website]) {
                groups = websiteStores[website];
                for (groupKey in groups) {
                    group = groups[groupKey];
                    optionGroup = document.createElement("OPTGROUP");
                    optionGroup.label = group["name"];  
                    sSel.appendChild(optionGroup);
                    
                    stores = group["stores"];
                    for (i=0; i < stores.length; i++) {
                        option = new Option();
                        option.value = stores[i]["id"];
                        option.text = stores[i]["name"];
                        optionGroup.appendChild(option);  
                    }
                }                 
            }
        }
        //]]></script>';

        return $result;
    }
}