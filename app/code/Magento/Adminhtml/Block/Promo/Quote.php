<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Catalog price rules
 *
 * @category    Magento
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Magento_Adminhtml_Block_Promo_Quote extends Magento_Adminhtml_Block_Widget_Grid_Container
{
    protected function _construct()
    {
        $this->_controller = 'promo_quote';
        $this->_headerText = Mage::helper('Magento_SalesRule_Helper_Data')->__('Shopping Cart Price Rules');
        $this->_addButtonLabel = Mage::helper('Magento_SalesRule_Helper_Data')->__('Add New Rule');
        parent::_construct();
        
    }
}
