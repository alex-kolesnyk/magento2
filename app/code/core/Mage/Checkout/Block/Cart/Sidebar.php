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
 * Wishlist sidebar block
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author     Ivan Chepurnyi <mitch@varien.com>
 */
class Mage_Checkout_Block_Cart_Sidebar extends Mage_Checkout_Block_Cart_Abstract
{
    public function getItems()
    {
        return Mage::getSingleton('checkout/cart')->getItems();
    }

    public function getItemCollection()
    {
        return $this->getQuote()->getItemsCollection();

        $collection = $this->getData('item_collection');
        if (is_null($collection)) {
            $collection = Mage::getModel('sales/quote_item')->getCollection()
               ->addAttributeToSelect('*')
               ->setQuote($this->getQuote())
               ->addAttributeToSort('created_at', 'desc')
               ->setPageSize(3)
               ->load();

            $this->setData('item_collection', $collection);
        }
        return $collection;
    }

    public function getSubtotal()
    {
        foreach ($this->getQuote()->getTotals() as $total) {
            if ($total->getCode()=='subtotal') {
                return Mage::helper('core')->currency($total->getValue());
            }
        }
        return false;
    }

    public function getCanDisplayCart()
    {
        return true;
    }

    public function getRemoveItemUrl($item)
    {
        return $this->helper('checkout/cart')->getRemoveUrl($item);
    }

    public function getMoveToWishlistItemUrl($item)
    {
        return $this->getUrl('checkout/cart/moveToWishlist',array('id'=>$item->getId()));
    }
}