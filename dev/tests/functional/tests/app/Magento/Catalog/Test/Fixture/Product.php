<?php
/**
 * {license_notice}
 *
 * @category    Mtf
 * @package     Mtf
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Catalog\Test\Fixture;

use Mtf\System\Config;
use Mtf\Factory\Factory;
use Mtf\Fixture\DataFixture;
use Mtf\Client\Driver\Selenium\Browser;
/**
 * Class Product
 *
 * @package Magento\Catalog\Test\Fixture
 */
class Product extends DataFixture
{
    /**
     * Attribute set for mapping data into ui tabs
     */
    const GROUP_PRODUCT_DETAILS     = 'product_info_tabs_product-details';
    const GROUP_ADVANCED_SEO        = 'product_info_tabs_search-optimization';
    const GROUP_PRODUCT_WEBSITE     = 'product_info_tabs_websites';
    const GROUP_PRODUCT_INVENTORY   = 'product_info_tabs_advanced-inventory';

    /**
     * Possible options used for visibility field
     */
    const VISIBILITY_NOT_VISIBLE = 'Not Visible Individually';
    const VISIBILITY_IN_CATALOG = 'Catalog';
    const VISIBILITY_IN_SEARCH = 'Search';
    const VISIBILITY_BOTH = 'Catalog, Search';

    /**
     * Custom constructor to create product with assigned category
     *
     * @param Config $configuration
     * @param array $placeholders
     */
    public function __construct(Config $configuration, $placeholders =  array())
    {
        parent::__construct($configuration, $placeholders);

        $this->_placeholders['category'] = array($this, 'categoryProvider');
    }

    /**
     * Create new category
     *
     * @return string
     */
    protected function categoryProvider()
    {
        return Factory::getFixtureFactory()->getMagentoCatalogCategory()
            ->switchData('subcategory')->persist()->getCategoryName();
    }

    /**
     * Get product name
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->getData('fields/name/value');
    }

    /**
     * Get product sku
     *
     * @return string
     */
    public function getProductSku()
    {
        return $this->getData('fields/sku/value');
    }

    /**
     * Get product price
     *
     * @return string
     */
    public function getProductPrice()
    {
        return $this->getData('fields/price/value');
    }

    /**
     * Get category name
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->getData('category_name');
    }

    /**
     * Get product url
     *
     * @return string
     */
    public function getProductUrl()
    {
        $fields = $this->getData('fields');
        if (isset($fields['url'])) {
            return $fields['url'];
        } else {
            return trim(strtolower(preg_replace('#[^0-9a-z]+#i', '-', $this->getProductName())), '-');
        }
    }

    /**
     * Get product id
     *
     * @return string
     */
    public function getProductId()
    {
        return $this->getData('fields/id/value');
    }

    /**
     * Create product
     */
    public function persist()
    {
        $id = Factory::getApp()->magentoCatalogCreateProduct($this);
        $this->_data['fields']['id']['value'] = $id;

        return $this;
    }

    /**
     * {inheritdoc}
     */
    protected function _initData()
    {
        $this->_defaultConfig = array(
            'block_form_class'  => '\\Magento\\Catalog\\Test\\Block\\Backend\\ProductForm',
            'block_grid_class'  => '\\Magento\\Catalog\\Test\\Block\\Backend\\ProductGrid',

            'grid_filter'       => array('name'),

            'url_create_page'   => 'admin/catalog_product/new',
            'url_update_page'   => 'admin/catalog_product/edit',
            'url_grid_page'     => 'admin/catalog_product/index'
        );

        $this->_repository = array(
            'simple'          => array(
                'config' => array(
                    'test_case' => array('Smart\\Crud', 'Smart\\E2e', 'CheckoutSuite\\Onepage'),
                    'constraint'        => 'Success',

                    'create_url_params' => array(
                        'type' => 'simple',
                        'set'  => 4,
                    )
                ),
                'data' => array(
                    'fields' => array(
                        'name'   => array(
                            'value' => 'Simple Product %isolation%',
                            'group' => static::GROUP_PRODUCT_DETAILS,
                            'curl'  => 'product[name]'
                        ),
                        'sku'    => array(
                            'value' => 'simple_sku_%isolation%',
                            'group' => static::GROUP_PRODUCT_DETAILS,
                            'curl'  => 'product[sku]'
                        ),
                        'price'  => array(
                            'value' => 10,
                            'group' => static::GROUP_PRODUCT_DETAILS,
                            'curl'  => 'product[price]'
                        ),
                        'tax_class_id' => array(
                            'value' => 'Taxable Goods',
                            'group' => static::GROUP_PRODUCT_DETAILS,
                            'input' => 'select',
                            'curl'  => 'product[tax_class_id]'
                        ),
                        'qty'    => array(
                            'value' => 1000,
                            'group' => static::GROUP_PRODUCT_DETAILS,
                            'curl'  => 'product[quantity_and_stock_status][qty]'
                        ),
                        'weight' => array(
                            'value' => '1',
                            'group' => static::GROUP_PRODUCT_DETAILS,
                            'curl'  => 'product[weight]'
                        ),
                        'meta_title' => array(
                            'value' => 'Meta Title',
                            'group' => static::GROUP_ADVANCED_SEO,
                            'curl'  => 'product[meta_title]'
                        ),
                        'meta_keyword' => array(
                            'value' => 'Meta Keyword',
                            'group' => static::GROUP_ADVANCED_SEO,
                            'curl'  => 'product[meta_keyword]'
                        ),
                        'meta_description' => array(
                            'value' => 'Meta Description',
                            'group' => static::GROUP_ADVANCED_SEO,
                            'curl'  => 'product[meta_description]'
                        ),
                        'product_website_1' => array(
                            'value' => 'Yes',
                            'group' => static::GROUP_PRODUCT_WEBSITE,
                            'input' => Browser::TYPIFIED_ELEMENT_CHECKBOX,
                            'curl'  => 'product[website_ids][]'
                        ),
                        'inventory_manage_stock' => array(
                            'value' => 'No',
                            'group' => static::GROUP_PRODUCT_INVENTORY,
                            'input' => Browser::TYPIFIED_ELEMENT_CHECKBOX,
                            'curl'  => 'product[stock_data][manage_stock]'
                        ),
                    )
                )
            )
        );
        $this->_repository['simple_with_category'] = $this->_repository['simple'];
        $this->_repository['simple_with_category']['data']['category_name'] = '%category%';

        //Default data set
        $this->switchData('simple');
    }

    /**
     * Get Url params
     *
     * @param string $urlKey
     * @return string
     */
    public function getUrlParams($urlKey)
    {
        $params = array();
        $config = $this->getDataConfig();
        if (!empty($config[$urlKey]) && is_array($config[$urlKey])) {
            foreach ($config[$urlKey] as $key => $value) {
                $params[] = $key .'/' .$value;
            }
        }
        return implode('/', $params);
    }

    /**
     * Returns data for curl POST params
     *
     * @return array
     */
    public function getPostParams()
    {
        $fields = $this->getData('fields');
        $params = array();
        foreach ($fields as $fieldId => $fieldData) {
            $params[isset($fieldData['curl']) ? $fieldData['curl'] : $fieldId] = $fieldData['value'];
        }
        return $params;
    }
}
