<?php

class Mage_CatalogExcel_Model_Mysql4_Export extends Mage_CatalogExcel_Model_Mysql4_Abstract
{
	public function fetchAttributes()
	{
		$attributeFields = array(
			'attribute_code', 
			'frontend_label', 'frontend_input', 'frontend_class', 'frontend_model', 
			'backend_type', 'backend_table', 'backend_model',
			'source_model', 'attribute_model',
			'is_visible', 'is_user_defined', 'is_global', 'is_required', 'is_unique',
			'is_visible_on_front', 'is_searchable', 'is_filterable', 'is_comparable',
			'default_value', 'apply_to', 'use_in_super_product',
		);
		
		$select = $this->getSelect()
			->from(array('et'=>$this->getTable('eav/entity_type')), 'entity_type_code')
			->join(array('a'=>$this->getTable('eav/attribute')), 'a.entity_type_id=et.entity_type_id', $attributeFields)
			->where('et.entity_type_code in (?)', array('catalog_product', 'catalog_category'))
			->order('if(not a.is_user_defined, 1, 2)')->order('attribute_code');
			
		$attributes = $this->getConnection()->fetchAll($select);
		
		return $attributes;
	}
	
	public function fetchAttributeSets()
	{
		$select = $this->getSelect()
			->from(array('et'=>$this->getTable('eav/entity_type')), 'entity_type_code')
			->join(array('s'=>$this->getTable('eav/attribute_set')), 's.entity_type_id=et.entity_type_id', 'attribute_set_name')
			->join(array('g'=>$this->getTable('eav/attribute_group')), 'g.attribute_set_id=s.attribute_set_id', 'attribute_group_name')
			->join(array('ea'=>$this->getTable('eav/entity_attribute')), 'ea.attribute_group_id=g.attribute_group_id', array())
			->join(array('a'=>$this->getTable('eav/attribute')), 'a.attribute_id=ea.attribute_id', 'attribute_code')
			->where('et.entity_type_code in (?)', array('catalog_product', 'catalog_category'))
			->order('et.entity_type_code')->order('s.sort_order')->order('g.sort_order');
			
		$sets = $this->getConnection()->fetchAll($select);
			
		return $sets;
	}
	
	public function fetchAttributeOptions()
	{
		$select = $this->getSelect()
			->from(array('et'=>$this->getTable('eav/entity_type')), 'entity_type_code')
			->join(array('a'=>$this->getTable('eav/attribute')), 'a.entity_type_id=et.entity_type_id', 'attribute_code')
			->join(array('ao'=>$this->getTable('eav/attribute_option')), 'ao.attribute_id=a.attribute_id', array())
			->where('et.entity_type_code in (?)', array('catalog_product', 'catalog_category'))
			->order('a.attribute_code')->order('ao.sort_order');
			
		$stores = Mage::getConfig()->getNode('stores')->children();
		foreach ($stores as $storeName=>$storeConfig) {
			$select->joinLeft(
				array($storeName=>$this->getTable('eav/attribute_option_value')), 
				"$storeName.option_id=ao.option_id and $storeName.store_id=".$storeConfig->descend('system/store/id'),
				array($storeName=>"$storeName.value")
			);
		}
		
		$options = $this->getConnection()->fetchAll($select);
		
		return $options;
	}

	public function fetchProductLinks()
	{
		$skuTable = $this->getTable('catalog/product').'_'.$this->getSkuAttribute('backend_type');
		$skuCond = ' and sku.store_id=0 and sku.attribute_id='.$this->getSkuAttribute('attribute_id');
		
		$select = $this->getSelect()
			->from(array('lt'=>$this->getTable('catalog/product_link_type')), array('link_type'=>'code'))
			->join(array('l'=>$this->getTable('catalog/product_link')), 'l.link_type_id=lt.link_type_id', array())
			->join(array('sku'=>$skuTable), 'sku.entity_id=l.product_id'.$skuCond, array('sku'=>'value'))
			->join(array('linked'=>$skuTable), 'linked.entity_id=l.product_id'.$skuCond, array('linked'=>'value'))
			->order('sku')->order('link_type');
			
		$links = $this->getConnection()->fetchAll($select);
		
		return $links;
	}
	
	public function fetchProductsInCategories()
	{
		$skuTable = $this->getTable('catalog/product').'_'.$this->getSkuAttribute('backend_type');
		$skuCond = ' and sku.store_id=0 and sku.attribute_id='.$this->getSkuAttribute('attribute_id');

		$select = $this->getSelect()
			->from(array('cp'=>$this->getTable('catalog/category_product')), array('category_id', 'position'))
			->join(array('sku'=>$skuTable), 'sku.entity_id=cp.product_id'.$skuCond, array('sku'=>'value'))
			->order('category_id')->order('position')->order('sku');
		
		$prodCats = $this->getConnection()->fetchAll($select);
		
		return $prodCats;
	}
	
	public function fetchProductsInStores()
	{
		$skuTable = $this->getTable('catalog/product').'_'.$this->getSkuAttribute('backend_type');
		$skuCond = ' and sku.store_id=0 and sku.attribute_id='.$this->getSkuAttribute('attribute_id');

		$select = $this->getSelect()
			->from(array('ps'=>$this->getTable('catalog/product_store')), array())
			->join(array('s'=>$this->getTable('core/store')), 's.store_id=ps.store_id', array('store'=>'code'))
			->join(array('sku'=>$skuTable), 'sku.entity_id=ps.product_id'.$skuCond, array('sku'=>'value'))
			->order('store')->order('sku');
			
		$prodStores = $this->getConnection()->fetchAll($select);
		
		return $prodStores;
	}
	
	public function fetchCategories()
	{
		$collection = Mage::getResourceModel('catalog/category_collection')
			->addAttributeToSelect('*')
			->load();
		
		$categories = array();
		foreach ($collection as $object) {
			$row = $object->getData();
			$categories[] = $row;
		}
		
		return $categories;
	}
	
	public function fetchProducts()
	{
		$attrSets = Mage::getResourceModel('eav/entity_attribute_set_collection')->load();
		$attrSetName = array();
		foreach ($attrSets as $attrSet) {
			$attrSetName[$attrSet->getId()] = $attrSet->getAttributeSetName();
		}
		
		$collection = Mage::getResourceModel('catalog/product_collection')
			->addAttributeToSelect('*')
			->load();
		
		$products = array();
		foreach ($collection as $object) {
			$r = $object->getData();
			
			unset($r['entity_id'], $r['entity_type_id']);
			$r['attribute_set_id'] = $attrSetName[$r['attribute_set_id']];
			
			$products[] = $r;
		}
		
		return $products;
	}
	
	public function fetchImageGallery()
	{
		return array();
	}
}