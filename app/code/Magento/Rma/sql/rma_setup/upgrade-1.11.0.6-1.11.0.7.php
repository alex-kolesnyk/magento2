<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Rma
 * @copyright   {copyright}
 * @license     {license_link}
 */

/** @var $this Magento_Rma_Model_Resource_Setup */
//Add Product's Attribute
$installer = $this->getCatalogResourceSetup(array('resourceName' => 'catalog_setup'));

$installer->removeAttribute(Magento_Catalog_Model_Product::ENTITY, 'is_returnable');
$installer->removeAttribute(Magento_Catalog_Model_Product::ENTITY, 'use_config_is_returnable');

$installer->addAttribute(Magento_Catalog_Model_Product::ENTITY, 'is_returnable', array(
    'group'             => 'General',
    'frontend'          => '',
    'label'             => 'Enable RMA',
    'input'             => 'select',
    'class'             => '',
    'source'            => 'Magento_Rma_Model_Product_Source',
    'global'            => Magento_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => Magento_Rma_Model_Product_Source::ATTRIBUTE_ENABLE_RMA_USE_CONFIG,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          =>
        Magento_Catalog_Model_Product_Type::TYPE_SIMPLE . ',' .
        Magento_Catalog_Model_Product_Type::TYPE_CONFIGURABLE . ',' .
        Magento_Catalog_Model_Product_Type::TYPE_GROUPED . ',' .
        Magento_Catalog_Model_Product_Type::TYPE_BUNDLE,
    'is_configurable'   => false,
    'input_renderer'    => 'Magento_Rma_Block_Adminhtml_Product_Renderer',
));
