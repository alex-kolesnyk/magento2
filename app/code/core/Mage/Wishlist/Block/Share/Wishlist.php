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
 * @package    Mage_Wishlist
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Wishlist block shared items
 *
 * @category   Mage
 * @package    Mage_Wishlist
 * @author	   Ivan Chepurnyi <mitch@varien.com>
 */

class Mage_Wishlist_Block_Share_Wishlist extends Mage_Core_Block_Template
{
	protected $_wishlistLoaded = false;
	protected $_customer = null;

	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('wishlist/shared.phtml');
        Mage::registry('action')->getLayout()->getBlock('root')->setHeaderTitle($this->getHeader());
	}

	public function getWishlist()
	{
		if(!$this->_wishlistLoaded) {
			Mage::registry('shared_wishlist')->getItemCollection()
				->addAttributeToSelect('name')
	            ->addAttributeToSelect('price')
	            ->addAttributeToSelect('image')
	            ->addAttributeToSelect('small_image')
	            ->addAttributeToFilter('store_id', array('in'=>Mage::registry('shared_wishlist')->getSharedStoreIds()))
				->load();

			$this->_wishlistLoaded = true;
		}

		return Mage::registry('shared_wishlist')->getItemCollection();
	}

	public function getWishlistCustomer()
	{
		if(is_null($this->_customer)) {
			$this->_customer = Mage::getModel('customer/customer')
				->load(Mage::registry('shared_wishlist')->getCustomerId());

		}

		return $this->_customer;
	}


	public function getEscapedDescription(Mage_Wishlist_Model_Item $item)
	{
		return htmlspecialchars($item->getDescription());
	}

	public function getHeader()
	{
		return __("%s's Wishlist", $this->getWishlistCustomer()->getFirstname());
	}

	public function getFormatedDate($date)
	{
		return strftime(Mage::getStoreConfig('general/local/datetime_format_medium'), strtotime($date));
	}
}// Class Mage_Wishlist_Block_Customer_Wishlist END
