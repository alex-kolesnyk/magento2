<?php
/**
 * Wishlist item collection
 *
 * @package    Mage
 * @subpackage Wishlist
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 * @license    http://www.opensource.org/licenses/osl-3.0.php
 * @author	   Ivan Chepurnyi <mitch@varien.com>
 */

class Mage_Wishlist_Model_Mysql4_Item_Collection extends Mage_Catalog_Model_Entity_Product_Collection 
{
	protected $_resource = null;
	
	public function __construct() 
	{
		$this->setEntity(Mage::getResourceSingleton('catalog/product'));
        $this->setObject('wishlist/item');
	}
	
	public function addWishlistFilter(Mage_Wishlist_Model_Wishlist	$wishlist)
	{
		// Workaround for entity_id. 
		$this->_joinFields['e_id'] = array('table'=>'e','field'=>'entity_id');
		
		$this->joinField('wishlist_item_id', 'wishlist/item', 'wishlist_item_id',  'product_id=e_id', array('wishlist_id'=>$wishlist->getId()))
			->joinField('product_id', 'wishlist/item', 'product_id',  'wishlist_item_id=wishlist_item_id')
			->joinField('description', 'wishlist/item' , 'description',  'wishlist_item_id=wishlist_item_id')
			->joinField('store_id', 'wishlist/item', 'store_id',  'wishlist_item_id=wishlist_item_id')
			->joinField('added_at', 'wishlist/item', 'added_at',  'wishlist_item_id=wishlist_item_id')
			->joinField('wishlist_id', 'wishlist/item', 'wishlist_id',  'wishlist_item_id=wishlist_item_id');
		
		return $this;
	}
		
	public function addStoreData()
	{
		if(!isset($this->_joinFields['e_id'])) {
			return $this;
		}
		
		$this->joinField('store_name', 'core/store', 'name', 'store_id=store_id')
			->joinField('days_in_wishlist', 'wishlist/item', "(TO_DAYS('" . now() . "') - TO_DAYS(".$this->_getAttributeTableAlias('days_in_wishlist').".added_at))", 'wishlist_item_id=wishlist_item_id');
		
		return $this;
	}
	
	protected function _getAttributeFieldName($attributeCode)
    {
    	if($attributeCode == 'days_in_wishlist') {
    		return $this->_joinFields[$attributeCode]['field'];
    	}
    	
    	return parent::_getAttributeFieldName($attributeCode);
    }
    	
}// Class Mage_Wishlist_Model_Mysql_Item_Collection END