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

class Mage_Adminhtml_Block_Permissions_Editroles extends Mage_Adminhtml_Block_Widget_Tabs {
    public function __construct()
    {
        parent::__construct();
        $this->setId('role_info_tabs');
        $this->setDestElementId('role_edit_form');
    }

    protected function _beforeToHtml()
    {
        $roleId = $this->_request->getParam('rid', false);
    	$role = Mage::getModel("permissions/roles")
    	   ->load($roleId);

    	$this->addTab('info', array(
            'label'     => __('Role Info'),
            'title'     => __('Role Info'),
            'content'   => $this->getLayout()->createBlock('adminhtml/permissions_tab_roleinfo')->setRole($role)->toHtml(),
            'active'    => true
        ));

        $this->addTab('account', array(
            'label'     => __('Role Resources'),
            'title'     => __('Role Resources'),
            'content'   => $this->getLayout()->createBlock('adminhtml/permissions_tab_rolesedit')->toHtml(),
        ));

        if( intval($roleId) > 0 ) {
            $this->addTab('roles', array(
                'label'     => __('Role Users'),
                'title'     => __('Role Users'),
                'content'   => $this->getLayout()->createBlock('adminhtml/permissions_tab_rolesusers')->toHtml(),
            ));
        }

        return parent::_beforeToHtml();
    }
}