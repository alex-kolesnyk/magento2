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
 * Product entity resource model
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Catalog_Model_Resource_Eav_Mysql4_Product extends Mage_Catalog_Model_Resource_Eav_Mysql4_Abstract
{
    protected $_productWebsiteTable;
    protected $_productCategoryTable;

    /**
     * Initialize resource
     */
    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->setType('catalog_product')
            ->setConnection(
                $resource->getConnection('catalog_read'),
                $resource->getConnection('catalog_write')
            );

        $this->_productWebsiteTable = $resource->getTableName('catalog/product_website');
        $this->_productCategoryTable= $resource->getTableName('catalog/category_product');
    }

    /**
     * Default product attributes
     *
     * @return array
     */
    protected function _getDefaultAttributes()
    {
        return array('entity_type_id', 'attribute_set_id', 'type_id', 'created_at', 'updated_at');
    }

    /**
     * Retrieve product website identifiers
     *
     * @param   $product
     * @return  Mage_Catalog_Model_Resource_Eav_Mysql4_Product
     */
    public function getWebsiteIds($product)
    {
        $select = $this->_getWriteAdapter()->select()
            ->from($this->_productWebsiteTable, 'website_id')
            ->where('product_id=?', $product->getId());
        return $this->_getWriteAdapter()->fetchCol($select);
    }

    /**
     * Retrieve product category identifiers
     *
     * @param   $product
     * @return  Mage_Catalog_Model_Resource_Eav_Mysql4_Product
     */
    public function getCategoryIds($product)
    {
        $select = $this->_getWriteAdapter()->select()
            ->from($this->_productCategoryTable, 'category_id')
            ->where('product_id=?', $product->getId());
        return $this->_getWriteAdapter()->fetchCol($select);
    }

    public function getIdBySku($sku)
    {
         return $this->_read->fetchOne('select entity_id from '.$this->getEntityTable().' where sku=?', $sku);
    }

    protected function _beforeSave(Varien_Object $object)
    {
        if (!$object->getId() && $object->getSku()) {
           $object->setId($this->getIdBySku($object->getSku()));
        }

        if (is_array($object->getData('category_ids'))) {
            $object->setData('category_ids', implode(',', $object->getData('category_ids')));
        }

        return parent::_beforeSave($object);
    }

    protected function _afterSave(Varien_Object $product)
    {
        parent::_afterSave($product);

        $this->_saveWebsiteIds($product)
            ->_saveCategories($product);

    	return $this;
    }

    /**
     * Save product website relations
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_Catalog_Model_Resource_Eav_Mysql4_Product
     */
    protected function _saveWebsiteIds($product)
    {
        $ids = $product->getWebsiteIds();

        $this->_getWriteAdapter()->delete(
            $this->_productWebsiteTable,
            $this->_getWriteAdapter()->quoteInto('product_id=?', $product->getId())
        );

        foreach ($ids as $websiteId) {
            $this->_getWriteAdapter()->insert(
                $this->_productWebsiteTable,
                array('product_id'=>$product->getId(), 'website_id'=>$websiteId)
            );
        }

        return $this;
    }

    /**
     * Save product category relations
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_Catalog_Model_Resource_Eav_Mysql4_Product
     */
    protected function _saveCategories(Varien_Object $object)
    {
        $categoryIds = $object->getCategoryIds();

        $oldCategoryIds = $object->getOrigData('category_ids');
        $oldCategoryIds = !empty($oldCategoryIds) ? explode(',', $oldCategoryIds) : array();

        $insert = array_diff($categoryIds, $oldCategoryIds);
        $delete = array_diff($oldCategoryIds, $categoryIds);

        $write = $this->_getWriteAdapter();

        if (!empty($insert)) {
            $insertSql = array();
            foreach ($insert as $v) {
                if (!empty($v)) {
                    $insertSql[] = '('.(int)$v.','.$object->getId().',0)';
                }
            };
            $write->query("insert into {$this->_productCategoryTable} (category_id, product_id, position) values ".join(',', $insertSql));
        }

        if (!empty($delete)) {
            $write->delete($this->_productCategoryTable,
                $write->quoteInto('product_id=?', $object->getId())
                .' and '.$write->quoteInto('category_id in (?)', $delete)
            );
        }

        return $this;
    }

    /**
     * Retrieve collection of product categories
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return unknown
     */
    public function getCategoryCollection($product)
    {
        $collection = Mage::getResourceModel('catalog/category_collection')
            ->joinField('product_id',
                'catalog/category_product',
                'product_id',
                'category_id=entity_id',
                null)
            ->addFieldToFilter('product_id', (int) $product->getId());
        return $collection;
    }


    public function getDefaultAttributeSourceModel()
    {
        return 'eav/entity_attribute_source_table';
    }

    /**
     * Validate all object's attributes against configuration
     *
     * @param Varien_Object $object
     * @return Varien_Object
     */
    public function validate($object)
    {
        parent::validate($object);
        return $this;
    }
}