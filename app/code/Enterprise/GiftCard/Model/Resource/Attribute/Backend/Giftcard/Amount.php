<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_GiftCard
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Giftcard Amount Backend Model
 *
 * @category    Enterprise
 * @package     Enterprise_GiftCard
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftCard_Model_Resource_Attribute_Backend_Giftcard_Amount extends Magento_Core_Model_Resource_Db_Abstract
{
    /**
     * Define main table and primary key
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_giftcard_amount', 'value_id');
    }

    /**
     * Load product data by product and attribute_id
     *
     * @param Magento_Catalog_Model_Product $product
     * @param Magento_Eav_Model_Entity_Attribute $attribute
     * @return array
     */
    public function loadProductData($product, $attribute)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from($this->getMainTable(), array(
                'website_id',
                'value'
            ))
            ->where('entity_id=:product_id')
            ->where('attribute_id=:attribute_id');
        $bind = array(
            'product_id'   => $product->getId(),
            'attribute_id' => $attribute->getId()
        );
        if ($attribute->isScopeGlobal()) {
            $select->where('website_id=0');
        } else {
            if ($storeId = $product->getStoreId()) {
                $select->where('website_id IN (0, :website_id)');
                $bind['website_id'] = Mage::app()->getStore($storeId)->getWebsiteId();
            }
        }
        return $read->fetchAll($select, $bind);
    }

    /**
     * Delete product data
     *
     * @param Magento_Catalog_Model_Product $product
     * @param Magento_Eav_Model_Entity_Attribute $attribute
     * @return Enterprise_GiftCard_Model_Resource_Attribute_Backend_Giftcard_Amount
     */
    public function deleteProductData($product, $attribute)
    {
        $condition = array();

        if (!$attribute->isScopeGlobal()) {
            if ($storeId = $product->getStoreId()) {
                $condition['website_id IN (?)'] = array(0, Mage::app()->getStore($storeId)->getWebsiteId());
            }
        }

        $condition['entity_id=?']    = $product->getId();
        $condition['attribute_id=?'] = $attribute->getId();

        $this->_getWriteAdapter()->delete($this->getMainTable(), $condition);
        return $this;
    }

    /**
     * Insert product data
     *
     * @param Magento_Catalog_Model_Product $product
     * @param array $data
     * @return Enterprise_GiftCard_Model_Resource_Attribute_Backend_Giftcard_Amount
     */
    public function insertProductData($product, $data)
    {
        $data['entity_id'] = $product->getId();
        $data['entity_type_id'] = $product->getEntityTypeId();

        $this->_getWriteAdapter()->insert($this->getMainTable(), $data);
        return $this;
    }
}
