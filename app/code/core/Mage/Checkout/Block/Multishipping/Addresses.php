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
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Multishipping checkout choose item addresses block
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Checkout_Block_Multishipping_Addresses extends Mage_Checkout_Block_Multishipping_Abstract
{
    protected function _initChildren()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle(__('Ship to Multiple Addresses') . ' - ' . $headBlock->getDefaultTitle());
        }
        return parent::_initChildren();
    }
    
    public function getItems()
    {
        $items = $this->getCheckout()->getQuoteShippingAddressesItems();
        $itemsFilter = new Varien_Filter_Object_Grid();
        $itemsFilter->addFilter(new Varien_Filter_Sprintf('%d'), 'qty');
        return $itemsFilter->filter($items);
    }
    
    /**
     * Retrieve HTML for addresses dropdown
     * 
     * @param  $item
     * @return string
     */
    public function getAddressesHtmlSelect($item, $index)
    {
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName('ship['.$index.']['.$item->getQuoteItemId().'][address]')
            ->setValue($item->getCustomerAddressId())
            ->setOptions($this->getAddressOptions());
            
        return $select->getHtml();
    }
    
    /**
     * Retrieve options for addresses dropdown
     * 
     * @return array
     */
    public function getAddressOptions()
    {
        $options = $this->getData('address_options');
        if (is_null($options)) {
            $options = array();
            foreach ($this->getCustomer()->getLoadedAddressCollection() as $address) {
                $options[] = array(
                    'value'=>$address->getId(), 
                    'label'=>$address->getFirstname().' '.$address->getLastname().', '.
                        $address->getStreet(-1).', '.
                        $address->getCity().', '.
                        $address->getRegion().', '.
                        $address->getCountry().' '.
                        $address->getPostcode(),
                );
            }
            $this->setData('address_options', $options);
        }
        return $options;
    }
    
    public function getCustomer()
    {
        return $this->getCheckout()->getCustomerSession()->getCustomer();
    }
    
    public function getItemUrl($item)
    {
        return $this->getUrl('catalog/product/view/id/'.$item->getProductId());
    }
    
    public function getItemDeleteUrl($item)
    {
        return $this->getUrl('*/*/removeItem', array('address'=>$item->getParentId(), 'id'=>$item->getId()));
    }
    
    public function getPostActionUrl()
    {
        return $this->getUrl('*/*/addressesPost');
    }
    
    public function getNewAddressUrl()
    {
        return Mage::getUrl('*/multishipping_address/newShipping');
    }
    
    public function getBackUrl()
    {
        return Mage::getUrl('*/cart/');
    }
}
