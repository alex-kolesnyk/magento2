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

require_once 'Varien/Pear/Package.php';

/**
 * Extension controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Moshe Gurvich <moshe@varien.com>
 */
class Mage_Adminhtml_Extensions_LocalController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('system/extensions/local');

        $this->_addContent($this->getLayout()->createBlock('adminhtml/extensions_local'));

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->loadLayout();

        $pkg = str_replace('|', '/', $this->getRequest()->getParam('id'));
        $ext = Mage::getModel('adminhtml/extension')->loadLocal($pkg);
        Mage::register('local_extension', $ext);
#echo "<pre>".print_r($ext->getData(),1)."</pre>";
        $this->_setActiveMenu('system/extensions/local');

        $this->_addContent($this->getLayout()->createBlock('adminhtml/extensions_local_edit'));
        $this->_addLeft($this->getLayout()->createBlock('adminhtml/extensions_local_edit_tabs'));

        $this->renderLayout();
    }

    public function upgradeAction()
    {
        $pkg = str_replace('|', '/', $this->getRequest()->getParam('id'));
        $pear = Varien_Pear::getInstance();
        $pear->run('upgrade', array(), array($pkg));
        echo '<pre>'; print_r($pear->getOutput()); echo '</pre>';
        echo '<pre>'; print_r($pear->getLog()); echo '</pre>';
    }

    public function uninstallAction()
    {
        $pkg = str_replace('|', '/', $this->getRequest()->getParam('id'));
        $pear = Varien_Pear::getInstance();
        $pear->run('uninstall', array(), array($pkg));
        echo '<pre>'; print_r($pear->getOutput()); echo '</pre>';
        echo '<pre>'; print_r($pear->getLog()); echo '</pre>';
    }
}