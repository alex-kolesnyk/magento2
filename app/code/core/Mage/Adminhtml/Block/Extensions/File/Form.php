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
 * Cache management form page
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Moshe Gurvich <moshe@varien.com>
 */
class Mage_Adminhtml_Block_Extensions_File_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function initForm()
    {
        $this->setTitle('Install Package File');
        $this->setTemplate('extensions/file/form.phtml');

        return $this;
    }

    public function getUploadInstallUrl()
    {
        return Mage::helper('adminhtml')->getUrl('*/*/install', array('do'=>'run', 'file_type'=>'local'));
    }

    public function getUploadButtonHtml()
    {
        $html = '';

        $html .= $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
            ->setClass('save')->setLabel($this->__('Upload and Install'))
            ->setOnClick("install('local')")
            ->toHtml();

        return $html;
    }

    public function getRemoteInstallUrl()
    {
        return Mage::helper('adminhtml')->getUrl('*/*/install', array('do'=>'run', 'file_type'=>'remote'));
    }

    public function getRemoteButtonHtml()
    {
        $html = '';

        $html .= $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
            ->setClass('save')->setLabel($this->__('Download and Install'))
            ->setOnClick("install('remote')")
            ->toHtml();

        return $html;
    }

    public function getBackButtonHtml()
    {
        $html = '';

        $html .= $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
            ->setClass('back')->setLabel($this->__('Back to local packages'))
            ->setOnClick("setLocation('" . Mage::helper('adminhtml')->getUrl('*/extensions_local') . "')")
            ->toHtml();

        return $html;
    }
}