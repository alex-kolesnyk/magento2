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
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog product options collection
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Option_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('catalog/product_option');
    }

    public function getOptions($store_id)
    {
/*
SELECT
	`main_table`.option_id,
	`main_table`.type,
	IFNULL(`option_title`.title,`default_option_title`.title) option_title,
	IFNULL(option_price.price, default_option_price.price) option_price,
	IFNULL(option_price.price_type, default_option_price.price_type) option_price_type
FROM
	catalog_product_option AS main_table
	left join catalog_product_option_price as default_option_price ON default_option_price.option_id=main_table.option_id AND (default_option_price.store_id='0')
	left join catalog_product_option_price as  option_price ON option_price.option_id=main_table.option_id AND option_price.store_id=1
	inner join catalog_product_option_title AS default_option_title ON default_option_title.option_id = main_table.option_id
	left join catalog_product_option_title AS option_title ON option_title.option_id = main_table.option_id AND option_title.store_id = 1
WHERE
	(product_id = '141') AND (default_option_title.store_id = '0')
*/

        $this->getSelect()
            ->joinLeft(array('default_option_price'=>$this->getTable('catalog/product_option_price')),
                '`default_option_price`.option_id=`main_table`.option_id AND '.$this->getConnection()->quoteInto('`default_option_price`.store_id=?',0),
                array('default_price'=>'price','default_price_type'=>'price_type'))
            ->joinLeft(array('store_option_price'=>$this->getTable('catalog/product_option_price')),
                '`store_option_price`.option_id=`main_table`.option_id AND '.$this->getConnection()->quoteInto('`store_option_price`.store_id=?', $store_id),
                array('store_price'=>'price','store_price_type'=>'price_type',
                'price'=>new Zend_Db_Expr('IFNULL(`store_option_price`.price,`default_option_price`.price)'),
                'price_type'=>new Zend_Db_Expr('IFNULL(`store_option_price`.price_type,`default_option_price`.price_type)')))
            ->join(array('default_option_title'=>$this->getTable('catalog/product_option_title')),
                '`default_option_title`.option_id=`main_table`.option_id',
                array('default_title'=>'title'))
            ->joinLeft(array('store_option_title'=>$this->getTable('catalog/product_option_title')),
                '`store_option_title`.option_id=`main_table`.option_id AND '.$this->getConnection()->quoteInto('`store_option_title`.store_id=?', $store_id),
                array('store_title'=>'title',
                'title'=>new Zend_Db_Expr('IFNULL(`store_option_title`.title,`default_option_title`.title)')))
            ->where('`default_option_title`.store_id=?', 0);

        return $this;
    }

    public function addProductToFilter($product)
    {
        if (empty($product)) {
            $this->addFieldToFilter('product_id', '');
        } elseif (is_array($product)) {
            $this->addFieldToFilter('product_id', array('in' => $product));
        } elseif ($product instanceof Mage_Catalog_Model_Product) {
            $this->addFieldToFilter('product_id', $product->getId());
        } else {
            $this->addFieldToFilter('product_id', $product);
        }

        return $this;
    }

    public function addValuesToResult()
    {
        $optionIds = array();
        foreach ($this as $option) {
            $optionIds[] = $option->getId();
        }
        if (!empty($optionIds)) {
            $values = Mage::getModel('catalog/product_option_value')
                ->getCollection()
                ->addOptionToFilter($optionIds);

            foreach ($values as $value) {
                $this->getItemById($value->getOptionId())->addValue($value);
            }
        }

        return $this;
    }
}