<?php
/**
 * API Product service.
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Catalog_Service_Product extends Mage_Core_Service_Abstract
{
    const SERVICE_ID = 'Mage_Catalog_Service_Product';

    /**
     * Return resource object or resource object data.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getItem($args = null, $asObject = true)
    {
        $result = $this->_getItem($args);
        if (!$asObject) {
            $result = $this->_getObjectData($result);
        }

        return $result;
    }

    /**
     * Returns model which operated by current service.
     *
     * @param Mage_Core_Service_Args $args
     * @return Mage_Catalog_Model_Product
     */
    protected function _getItem(Mage_Core_Service_Args $args)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = $this->_objectManager->create('Mage_Catalog_Model_Product');

        $id = $args->getId();
        // TODO: try as `sku` first (can be voided if we won't be supporting numeric SKUs)
        $productId = $product->getIdBySku($id);
        if (false === $productId) {
            if (is_numeric($id)) {
                $productId = $id;
            }
        }

        // `set` methods are creating troubles
        foreach ($args->getData() as $k => $v) {
            $product->setDataUsingMethod($k, $v);
        }

        if (false !== $productId) {
            // TODO: Depends on MDS-167
            //$fieldset = $args->getFieldset();
            //$product->setFieldset($fieldset);

            $product->load($productId);
        }

        if (!$product->getId()) {
            // TODO: so what to do?
            //assumption:
            $product->unsetData();
        } elseif (!$product->isVisibleInCatalog() || !$product->isVisibleInSiteVisibility()) {
            // TODO: so what to do?
            //assumption:
            $product->unsetData();
        } elseif (!in_array(Mage::app()->getStore()->getWebsiteId(), $product->getWebsiteIds())) {
            // TODO: so what to do?
            //assumption:
            $product->unsetData();
        }

        return $product;
    }

    /**
     * Returns collection of resource objects.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getItems($args = null, $asObject = true)
    {
        $result = $this->_getItems($args);
        if (!$asObject) {
            $result = $this->_getCollectionData($result);
        }

        return $result;
    }

    /**
     * Get collection object of the current service
     *
     * @param Mage_Core_Service_Args $args
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _getItems(Mage_Core_Service_Args $args)
    {
        $collection = Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Collection');

        // Depends on MDS-167
        //$fieldset = $args->getFieldset();
        // $collection->setFieldset($fieldsetId);

        $productIds = $args->getProductIds();
        $collection->addIdFilter($productIds);

        $filters = $args->getFilters();
        $collection->addAttributeToFilter($filters);

        // TODO or not TODO
        //$collection->load();

        return $collection;
    }
}
