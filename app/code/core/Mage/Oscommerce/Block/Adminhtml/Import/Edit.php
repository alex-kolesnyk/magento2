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
 * @package    Mage_Oscommerce
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * osCommerce convert edit block
 * 
 * @author     Kyaw Soe Lynn Maung <vincent@varien.com>
 */

class Mage_Oscommerce_Block_Adminhtml_Import_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_blockGroup = 'oscommerce';

    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_import';
        $this->_updateButton('save', 'label', Mage::helper('adminhtml')->__('Save Profile'));
        $this->_updateButton('delete', 'label', Mage::helper('adminhtml')->__('Delete Profile'));
        parent::__construct();
    }

    public function getHeaderText()
    {
        if (Mage::registry('current_convert_osc')->getId()) { // TOCHECK
            return Mage::helper('adminhtml')->__('Edit osCommerce Profile :: %s', Mage::registry('current_convert_osc')->getName());
        }
        else {
            return Mage::helper('adminhtml')->__('New osCommerce Profile');
        }
    }
}