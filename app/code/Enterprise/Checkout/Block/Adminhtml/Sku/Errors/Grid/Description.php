<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Checkout
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Block with description of why item has not been added to ordered items list
 *
 * @method Magento_Object                                                   getItem()
 * @method Magento_Catalog_Model_Product                                      getProduct()
 * @method Enterprise_Checkout_Block_Adminhtml_Sku_Errors_Grid_Description setItem()
 * @method Enterprise_Checkout_Block_Adminhtml_Sku_Errors_Grid_Description setProduct()
 *
 * @category    Enterprise
 * @package     Enterprise_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Checkout_Block_Adminhtml_Sku_Errors_Grid_Description extends Magento_Adminhtml_Block_Template
{
    protected $_template = 'sku/errors/grid/description.phtml';

    /**
     * Retrieves HTML code of "Configure" button
     *
     * @return string
     */
    public function getConfigureButtonHtml()
    {
        $canConfigure = $this->getProduct()->canConfigure() && !$this->getItem()->getIsConfigureDisabled();
        $productId = $this->escapeHtml(Mage::helper('Magento_Core_Helper_Data')->jsonEncode($this->getProduct()->getId()));
        $itemSku = $this->escapeHtml(Mage::helper('Magento_Core_Helper_Data')->jsonEncode($this->getItem()->getSku()));

        /* @var $button Magento_Adminhtml_Block_Widget_Button */
        $button = $this->getLayout()->createBlock('Magento_Adminhtml_Block_Widget_Button', '', array('data' => array(
            'class'    => $canConfigure ? 'action-configure' : 'action-configure action-disabled',
            'onclick'  => $canConfigure ? "addBySku.configure({$productId}, {$itemSku})" : '',
            'disabled' => !$canConfigure,
            'label'    => __('Configure'),
            'type'     => 'button',
        )));

        return $button->toHtml();
    }

    /**
     * Retrieve HTML name for element
     *
     * @return string
     */
    public function getSourceId()
    {
        return $this->_prepareLayout()->getLayout()->getBlock('sku_error_grid')->getId();
    }

    /**
     * Returns error message of the item
     * @see Enterprise_Checkout_Helper_Data::ADD_ITEM_STATUS_FAILED_* constants for $code
     *
     * @param Magento_Object $item
     * @return string
     */
    public function getErrorMessage($item)
    {
        return Mage::helper('Enterprise_Checkout_Helper_Data')->getMessageByItem($item);
    }
}
