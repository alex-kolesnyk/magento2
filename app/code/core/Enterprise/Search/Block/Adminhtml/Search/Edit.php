<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Search
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Search queries relations grid container
 *
 * @category   Enterprise
 * @package    Enterprise_Search
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Search_Block_Adminhtml_Search_Edit extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Enable grid container
     *
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Enterprise_Search';
        $this->_controller = 'adminhtml_search';
        $this->_headerText = Mage::helper('Enterprise_Search_Helper_Data')->__('Related Search Terms');
        $this->_addButtonLabel = Mage::helper('Enterprise_Search_Helper_Data')->__('Add New Search Term');
        parent::_construct();
        $this->_removeButton('add');
    }

}
