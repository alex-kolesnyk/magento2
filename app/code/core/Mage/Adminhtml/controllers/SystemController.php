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
 * System admin controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Adminhtml_SystemController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('system');
        $this->_addBreadcrumb(__('System'), __('System'));
        $this->renderLayout();
    }

    public function setStoreAction()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        $referer = $this->getRequest()->getServer('HTTP_REFERER', Mage::getUrl('*'));

        if ($storeId) {
            Mage::getSingleton('adminhtml/session')->setStoreId($storeId);
        }
        $this->getResponse()->setRedirect($referer);
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system');
    }
}