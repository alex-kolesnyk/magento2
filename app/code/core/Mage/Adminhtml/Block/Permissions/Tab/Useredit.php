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

class Mage_Adminhtml_Block_Permissions_Tab_Useredit extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
    }

    public function _beforeToHtml() {
    	$this->_initForm();

    	return parent::_beforeToHtml();
    }

    protected function _initForm()
    {
        $form = new Varien_Data_Form();

        $user = $this->getUser();
        $userId = false;
        if (!empty($user)) {
            $userId = $user->getUserId();
        }

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>__('Account Information')));

        $fieldset->addField('username', 'text',
            array(
                'name'  => 'username',
                'label' => __('User Name'),
                'id'    => 'username',
                'title' => __('User Name'),
                'class' => 'required-entry',
                'required' => true,
            )
        );

        $fieldset->addField('firstname', 'text',
            array(
                'name'  => 'firstname',
                'label' => __('First Name'),
                'id'    => 'firstname',
                'title' => __('First Name'),
                'class' => 'required-entry',
                'required' => true,
            )
        );

        $fieldset->addField('lastname', 'text',
            array(
                'name'  => 'lastname',
                'label' => __('Last Name'),
                'id'    => 'lastname',
                'title' => __('Last Name'),
                'class' => 'required-entry',
                'required' => true,
            )
        );

        $fieldset->addField('user_id', 'hidden',
            array(
                'name'  => 'user_id',
                'id'    => 'user_id',
            )
        );

        $fieldset->addField('email', 'text',
            array(
                'name'  => 'email',
                'label' => __('Email'),
                'id'    => 'customer_email',
                'title' => __('User Email'),
                'class' => 'required-entry validate-email',
                'required' => true,
            )
        );

        if (!empty($user)) {
            $this->setValues($user->toArray());
        }

        if ($userId) {
            $fieldset->addField('password', 'password',
                array(
                    'name'  => 'new_password',
                    'label' => __('New Password'),
                    'id'    => 'new_pass',
                    'title' => __('New Password'),
                    'class' => 'input-text validate-password',
                )
            );

            $fieldset->addField('confirmation', 'password',
                array(
                    'name'  => 'password_confirmation',
                    'label' => __('Password Confirmation'),
                    'id'    => 'confirmation',
                    'class' => 'input-text validate-cpassword',
                )
            );
        }
        else {
           $fieldset->addField('password', 'password',
                array(
                    'name'  => 'password',
                    'label' => __('Password'),
                    'id'    => 'customer_pass',
                    'title' => __('Password'),
                    'class' => 'input-text required-entry validate-password',
                    'required' => true,
                )
            );
           $fieldset->addField('confirmation', 'password',
                array(
                    'name'  => 'password_confirmation',
                    'label' => __('Password Confirmation'),
                    'id'    => 'confirmation',
                    'title' => __('Password Confirmation'),
                    'class' => 'input-text required-entry validate-cpassword',
                    'required' => true,
                )
            );
        }

        $fieldset->addField('is_active', 'select',
            array(
                'name'  	=> 'is_active',
                'label' 	=> __('This account is'),
                'id'    	=> 'is_active',
                'title' 	=> __('Account status'),
                'class' 	=> 'input-select',
                'required' 	=> false,
                'style'		=> 'width: 80px',
                'value'		=> '1',
                'values'	=> array(
                	array(
	                	'label' => __('Active'),
	                	'value'	=> '1',
                	),
                	array(
	                	'label' => __('Inactive'),
	                	'value' => '0',
                	),
                ),
            )
        );

        $data = $this->getUser()->getData();

        unset($data['password']);

        $form->setValues($data);
        $this->setForm($form);
    }
}
