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

class Mage_Adminhtml_Block_System_Design_Edit extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('system/design/edit.phtml');
        $this->setId('design_edit');
    }

    protected function _prepareLayout()
    {
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('core')->__('Back'),
                    'onclick'   => 'setLocation(\''.Mage::helper('adminhtml')->getUrl('*/*/').'\')',
                    'class' => 'back'
                ))
        );

        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('core')->__('Save'),
                    'onclick'   => 'designForm.submit()',
                    'class' => 'save'
                ))
        );

        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('core')->__('Delete'),
                    'onclick'   => 'confirmSetLocation(\''.Mage::helper('core')->__('Are you sure?').'\', \''.$this->getDeleteUrl().'\')',
                    'class'  => 'delete'
                ))
        );
        return parent::_prepareLayout();
    }

    public function getDesignChangeId()
    {
        return Mage::registry('design')->getId();
    }

    public function getDeleteUrl()
    {
        return Mage::helper('adminhtml')->getUrl('*/*/delete', array('_current'=>true));
    }

    public function getSaveUrl()
    {
        return Mage::helper('adminhtml')->getUrl('*/*/save', array('_current'=>true));
    }

    public function getValidationUrl()
    {
        return Mage::helper('adminhtml')->getUrl('*/*/validate', array('_current'=>true));
    }

    public function getHeader()
    {
        $header = '';
        if (Mage::registry('design')->getId()) {
            $header = Mage::helper('core')->__('Edit Design Change');
        } else {
            $header = Mage::helper('core')->__('New Design Change');
        }
        return $header;
    }
}