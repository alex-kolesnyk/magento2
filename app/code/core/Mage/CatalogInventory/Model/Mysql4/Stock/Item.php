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
 * @package    Mage_CatalogInventory
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
/**
 * Stock item resource model
 *
 * @category   Mage
 * @package    Mage_CatalogInventory
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_CatalogInventory_Model_Mysql4_Stock_Item extends Mage_Core_Model_Mysql4_Abstract
{
    protected function  _construct() 
    {
        $this->_init('cataloginventory/stock_item', 'item_id');
    }
    
    /**
     * Loading stock item data by product
     *
     * @param   Mage_CatalogInventory_Model_Stock_Item $item
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_Core_Model_Mysql4_Abstract
     */
    public function loadByProduct(Mage_CatalogInventory_Model_Stock_Item $item, Mage_Catalog_Model_Product $product)
    {
        $select = $this->_getLoadSelect('product_id', $product->getId(), $item)
            ->where('stock_id=?', $item->getStockId());
            
        $item->setData($this->getConnection('read')->fetchRow($select));
        $this->_afterLoad($item);
        return $this;
    }
}