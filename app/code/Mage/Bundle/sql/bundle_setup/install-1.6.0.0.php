<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @copyright   {copyright}
 * @license     {license_link}
 */

$installer = $this;
/** @var $installer Magento_Catalog_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'catalog_product_bundle_option'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_bundle_option'))
    ->addColumn('option_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Option Id')
    ->addColumn('parent_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Parent Id')
    ->addColumn('required', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Required')
    ->addColumn('position', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Position')
    ->addColumn('type', Magento_DB_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Type')
    ->addIndex($installer->getIdxName('catalog_product_bundle_option', array('parent_id')),
        array('parent_id'))
    ->addForeignKey($installer->getFkName('catalog_product_bundle_option', 'parent_id', 'catalog_product_entity', 'entity_id'),
        'parent_id', $installer->getTable('catalog_product_entity'), 'entity_id',
        Magento_DB_Ddl_Table::ACTION_CASCADE, Magento_DB_Ddl_Table::ACTION_CASCADE)
    ->setComment('Catalog Product Bundle Option');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_bundle_option_value'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_bundle_option_value'))
    ->addColumn('value_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Value Id')
    ->addColumn('option_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Option Id')
    ->addColumn('store_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Store Id')
    ->addColumn('title', Magento_DB_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Title')
    ->addIndex($installer->getIdxName('catalog_product_bundle_option_value', array('option_id', 'store_id'), Magento_DB_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('option_id', 'store_id'), array('type' => Magento_DB_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addForeignKey($installer->getFkName('catalog_product_bundle_option_value', 'option_id', 'catalog_product_bundle_option', 'option_id'),
        'option_id', $installer->getTable('catalog_product_bundle_option'), 'option_id',
        Magento_DB_Ddl_Table::ACTION_CASCADE, Magento_DB_Ddl_Table::ACTION_CASCADE)
    ->setComment('Catalog Product Bundle Option Value');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_bundle_selection'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_bundle_selection'))
    ->addColumn('selection_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Selection Id')
    ->addColumn('option_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Option Id')
    ->addColumn('parent_product_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Parent Product Id')
    ->addColumn('product_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Product Id')
    ->addColumn('position', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Position')
    ->addColumn('is_default', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Default')
    ->addColumn('selection_price_type', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Selection Price Type')
    ->addColumn('selection_price_value', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Selection Price Value')
    ->addColumn('selection_qty', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Selection Qty')
    ->addColumn('selection_can_change_qty', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Selection Can Change Qty')
    ->addIndex($installer->getIdxName('catalog_product_bundle_selection', array('option_id')),
        array('option_id'))
    ->addIndex($installer->getIdxName('catalog_product_bundle_selection', array('product_id')),
        array('product_id'))
    ->addForeignKey($installer->getFkName('catalog_product_bundle_selection', 'option_id', 'catalog_product_bundle_option', 'option_id'),
        'option_id', $installer->getTable('catalog_product_bundle_option'), 'option_id',
        Magento_DB_Ddl_Table::ACTION_CASCADE, Magento_DB_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('catalog_product_bundle_selection', 'product_id', 'catalog_product_entity', 'entity_id'),
        'product_id', $installer->getTable('catalog_product_entity'), 'entity_id',
        Magento_DB_Ddl_Table::ACTION_CASCADE, Magento_DB_Ddl_Table::ACTION_CASCADE)
    ->setComment('Catalog Product Bundle Selection');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_bundle_selection_price'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_bundle_selection_price'))
    ->addColumn('selection_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Selection Id')
    ->addColumn('website_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Website Id')
    ->addColumn('selection_price_type', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Selection Price Type')
    ->addColumn('selection_price_value', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Selection Price Value')
    ->addIndex($installer->getIdxName('catalog_product_bundle_selection_price', array('website_id')),
        array('website_id'))
    ->addForeignKey($installer->getFkName('catalog_product_bundle_selection_price', 'website_id', 'core_website', 'website_id'),
        'website_id', $installer->getTable('core_website'), 'website_id',
        Magento_DB_Ddl_Table::ACTION_CASCADE, Magento_DB_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('catalog_product_bundle_selection_price', 'selection_id', 'catalog_product_bundle_selection', 'selection_id'),
        'selection_id', $installer->getTable('catalog_product_bundle_selection'), 'selection_id',
        Magento_DB_Ddl_Table::ACTION_CASCADE, Magento_DB_Ddl_Table::ACTION_CASCADE)
    ->setComment('Catalog Product Bundle Selection Price');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_bundle_price_index'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_bundle_price_index'))
    ->addColumn('entity_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('website_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Website Id')
    ->addColumn('customer_group_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Customer Group Id')
    ->addColumn('min_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        ), 'Min Price')
    ->addColumn('max_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        ), 'Max Price')
    ->addIndex($installer->getIdxName('catalog_product_bundle_price_index', array('website_id')),
        array('website_id'))
    ->addIndex($installer->getIdxName('catalog_product_bundle_price_index', array('customer_group_id')),
        array('customer_group_id'))
    ->addForeignKey($installer->getFkName('catalog_product_bundle_price_index', 'customer_group_id', 'customer_group', 'customer_group_id'),
        'customer_group_id', $installer->getTable('customer_group'), 'customer_group_id',
        Magento_DB_Ddl_Table::ACTION_CASCADE, Magento_DB_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('catalog_product_bundle_price_index', 'entity_id', 'catalog_product_entity', 'entity_id'),
        'entity_id', $installer->getTable('catalog_product_entity'), 'entity_id',
        Magento_DB_Ddl_Table::ACTION_CASCADE, Magento_DB_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('catalog_product_bundle_price_index', 'website_id', 'core_website', 'website_id'),
        'website_id', $installer->getTable('core_website'), 'website_id',
        Magento_DB_Ddl_Table::ACTION_CASCADE, Magento_DB_Ddl_Table::ACTION_CASCADE)
    ->setComment('Catalog Product Bundle Price Index');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_bundle_stock_index'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_bundle_stock_index'))
    ->addColumn('entity_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('website_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Website Id')
    ->addColumn('stock_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Stock Id')
    ->addColumn('option_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Option Id')
    ->addColumn('stock_status', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'default'   => '0',
        ), 'Stock Status')
    ->setComment('Catalog Product Bundle Stock Index');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_bundle_idx'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_index_price_bundle_idx'))
    ->addColumn('entity_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('customer_group_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Customer Group Id')
    ->addColumn('website_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Website Id')
    ->addColumn('tax_class_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Tax Class Id')
    ->addColumn('price_type', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Price Type')
    ->addColumn('special_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Special Price')
    ->addColumn('tier_percent', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tier Percent')
    ->addColumn('orig_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Orig Price')
    ->addColumn('price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price')
    ->addColumn('min_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Min Price')
    ->addColumn('max_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Max Price')
    ->addColumn('tier_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tier Price')
    ->addColumn('base_tier', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Tier')
    ->setComment('Catalog Product Index Price Bundle Idx');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_bundle_tmp'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_index_price_bundle_tmp'))
    ->addColumn('entity_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('customer_group_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Customer Group Id')
    ->addColumn('website_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Website Id')
    ->addColumn('tax_class_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Tax Class Id')
    ->addColumn('price_type', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Price Type')
    ->addColumn('special_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Special Price')
    ->addColumn('tier_percent', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tier Percent')
    ->addColumn('orig_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Orig Price')
    ->addColumn('price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price')
    ->addColumn('min_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Min Price')
    ->addColumn('max_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Max Price')
    ->addColumn('tier_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tier Price')
    ->addColumn('base_tier', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Tier')
    ->setComment('Catalog Product Index Price Bundle Tmp');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_bundle_sel_idx'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_index_price_bundle_sel_idx'))
    ->addColumn('entity_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('customer_group_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Customer Group Id')
    ->addColumn('website_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Website Id')
    ->addColumn('option_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Option Id')
    ->addColumn('selection_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Selection Id')
    ->addColumn('group_type', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Group Type')
    ->addColumn('is_required', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Is Required')
    ->addColumn('price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price')
    ->addColumn('tier_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tier Price')
    ->setComment('Catalog Product Index Price Bundle Sel Idx');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_bundle_sel_tmp'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_index_price_bundle_sel_tmp'))
    ->addColumn('entity_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('customer_group_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Customer Group Id')
    ->addColumn('website_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Website Id')
    ->addColumn('option_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Option Id')
    ->addColumn('selection_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Selection Id')
    ->addColumn('group_type', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Group Type')
    ->addColumn('is_required', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Is Required')
    ->addColumn('price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price')
    ->addColumn('tier_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tier Price')
    ->setComment('Catalog Product Index Price Bundle Sel Tmp');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_bundle_opt_idx'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_index_price_bundle_opt_idx'))
    ->addColumn('entity_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('customer_group_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Customer Group Id')
    ->addColumn('website_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Website Id')
    ->addColumn('option_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Option Id')
    ->addColumn('min_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Min Price')
    ->addColumn('alt_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Alt Price')
    ->addColumn('max_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Max Price')
    ->addColumn('tier_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tier Price')
    ->addColumn('alt_tier_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Alt Tier Price')
    ->setComment('Catalog Product Index Price Bundle Opt Idx');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_bundle_opt_tmp'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_index_price_bundle_opt_tmp'))
    ->addColumn('entity_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('customer_group_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Customer Group Id')
    ->addColumn('website_id', Magento_DB_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Website Id')
    ->addColumn('option_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Option Id')
    ->addColumn('min_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Min Price')
    ->addColumn('alt_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Alt Price')
    ->addColumn('max_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Max Price')
    ->addColumn('tier_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tier Price')
    ->addColumn('alt_tier_price', Magento_DB_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Alt Tier Price')
    ->setComment('Catalog Product Index Price Bundle Opt Tmp');
$installer->getConnection()->createTable($table);

/**
 * Add attributes to the eav/attribute 
 */
$installer->addAttribute(Magento_Catalog_Model_Product::ENTITY, 'price_type', array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '',
        'input'             => '',
        'class'             => '',
        'source'            => '',
        'global'            => Magento_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => false,
        'required'          => true,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'used_in_product_listing' => true,
        'unique'            => false,
        'apply_to'          => 'bundle',
        'is_configurable'   => false
    ));

$installer->addAttribute(Magento_Catalog_Model_Product::ENTITY, 'sku_type', array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '',
        'input'             => '',
        'class'             => '',
        'source'            => '',
        'global'            => Magento_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => false,
        'required'          => true,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'bundle',
        'is_configurable'   => false
    ));

$installer->addAttribute(Magento_Catalog_Model_Product::ENTITY, 'weight_type', array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '',
        'input'             => '',
        'class'             => '',
        'source'            => '',
        'global'            => Magento_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => false,
        'required'          => true,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'used_in_product_listing' => true,
        'unique'            => false,
        'apply_to'          => 'bundle',
        'is_configurable'   => false
    ));

$installer->addAttribute(Magento_Catalog_Model_Product::ENTITY, 'price_view', array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Price View',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'Mage_Bundle_Model_Product_Attribute_Source_Price_View',
        'global'            => Magento_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => true,
        'required'          => true,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'used_in_product_listing' => true,
        'unique'            => false,
        'apply_to'          => 'bundle',
        'is_configurable'   => false
    ));

$installer->addAttribute(Magento_Catalog_Model_Product::ENTITY, 'shipment_type', array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Shipment',
        'input'             => '',
        'class'             => '',
        'source'            => '',
        'global'            => Magento_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => false,
        'required'          => true,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'used_in_product_listing' => true,
        'unique'            => false,
        'apply_to'          => 'bundle',
        'is_configurable'   => false
    ));

$installer->endSetup();
