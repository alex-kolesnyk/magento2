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
 * Form block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Adminhtml_Block_Widget_Form extends Mage_Adminhtml_Block_Widget
{
    protected $_form;
    //protected $_elementBlock;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('widget/form.phtml');
        $this->setDestElementId('edit_form');
        $this->setShowGlobalIcon(false);
    }

    public function getForm()
    {
        return $this->_form;
    }

    public function getFormObject()
    {
        return $this->getForm();
    }

    public function getFormHtml()
    {
        if (is_object($this->getForm())) {
            return $this->getForm()->getHtml();
        }
        return '';
    }

    public function setForm(Varien_Data_Form $form)
    {
        $this->_form = $form;
        $this->_form->setParent($this);
        $this->_form->setBaseUrl(Mage::getBaseUrl());
        return $this;
    }

    protected function _prepareForm()
    {
        return $this;
    }

    protected function _beforeToHtml()
    {
        $this->_prepareForm();
        return parent::_beforeToHtml();
    }

    protected function _setFieldset($attributes, $fieldset)
    {
        $this->_addElementTypes($fieldset);
        foreach ($attributes as $attribute) {
            if (!$attribute->getIsVisible()) {
                continue;
            }
            if ($inputType = $attribute->getFrontend()->getInputType()) {
                $element = $fieldset->addField($attribute->getAttributeCode(), $inputType,
                    array(
                        'name'  => $attribute->getAttributeCode(),
                        'label' => $attribute->getFrontend()->getLabel(),
                        'class' => $attribute->getFrontend()->getClass(),
                        'required' => $attribute->getIsRequired(),
                    )
                )
                ->setEntityAttribute($attribute);

                if ($this->getShowGlobalIcon() && $attribute->getIsGlobal()) {
                    $element->setAfterElementHtml(
                        '<img src="'.$this->getSkinUrl('images/fam_link.gif').'" alt="'.__('Global Attribute').'" title="'.__('This attribute shares the same value in all the stores').'" class="attribute-global"/>'
                    );
                }

                if ($inputType == 'select' || $inputType == 'multiselect') {
                    $element->setValues($attribute->getFrontend()->getSelectOptions());
                }
                
                if ($inputType == 'date') {
                    $element->setImage($this->getSkinUrl('images/grid-cal.gif'));
                }
            }
        }
    }
    
    protected function _addElementTypes(Varien_Data_Form_Abstract $baseElement)
    {
        $types = $this->_getAdditionalElementTypes();
        foreach ($types as $code => $className) {
        	$baseElement->addType($code, $className);
        }
    }
    
    protected function _getAdditionalElementTypes()
    {
        return array();
    }
}
