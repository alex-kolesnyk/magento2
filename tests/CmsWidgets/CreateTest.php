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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Create Widget Test
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CmsWidgets_CreateTest extends Mage_Selenium_TestCase
{
    protected static $products = array();

    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    protected function assertPreconditions()
    {
        $this->addParameter('id', '0');
    }

    /**
     * <p>Preconditions</p>
     * <p>Creates Category to use during tests</p>
     *
     * @test
     */
    public function createCategory()
    {
        $this->navigate('manage_categories');
        $this->categoryHelper()->checkCategoriesPage();
        $rootCat = 'Default Category';
        $categoryData = $this->loadData('sub_category_required', null, 'name');
        $this->categoryHelper()->createSubCategory($rootCat, $categoryData);
        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);
        $this->categoryHelper()->checkCategoriesPage();

        return $rootCat . '/' . $categoryData['name'];
    }

    /**
     * <p>Preconditions</p>
     * <p>Creates Attribute (dropdown) to use during tests</p>
     *
     * @test
     */
    public function createAttribute()
    {
        $attrData = $this->loadData('product_attribute_dropdown_with_options', NULL,
                array('admin_title', 'attribute_code'));
        $associatedAttributes = $this->loadData('associated_attributes',
                array('General' => $attrData['attribute_code']));
        $this->navigate('manage_attributes');
        $this->productAttributeHelper()->createAttribute($attrData);
        $this->assertTrue($this->successMessage('success_saved_attribute'), $this->messages);
        $this->navigate('manage_attribute_sets');
        $this->attributeSetHelper()->openAttributeSet();
        $this->attributeSetHelper()->addAttributeToSet($associatedAttributes);
        $this->saveForm('save_attribute_set');
        $this->assertTrue($this->successMessage('success_attribute_set_saved'), $this->messages);

        return $attrData;
    }

    /**
     * Create required products for testing
     *
     * @dataProvider dataProductTypes
     * @depends createCategory
     * @depends createAttribute
     *
     * @test
     */
    public function createProducts($dataProductType, $category, $attrData)
    {
        $this->navigate('manage_products');
        //Data
        if ($dataProductType == 'configurable') {
            $productData = $this->loadData($dataProductType . '_product_required',
                array('configurable_attribute_title' => $attrData['admin_title'],
                    'categories' => $category), array('general_sku', 'general_name'));
        } else {
            $productData = $this->loadData($dataProductType . '_product_required',
                array('categories' => $category), array('general_name', 'general_sku'));
        }
        //Steps
        $this->productHelper()->createProduct($productData, $dataProductType);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_product'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_products'), $this->messages);
        self::$products['sku'][$dataProductType] = $productData['general_sku'];
        self::$products['name'][$dataProductType] = $productData['general_name'];
    }

    public function dataProductTypes()
    {
        return array(
            array('simple'),
            array('grouped'),
            array('configurable'),
            array('virtual'),
            array('bundle'),
            array('downloadable')
        );
    }

    /**
     * <p>Creates All Types of widgets</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Manage Widgets page</p>
     * <p>2. Create all types of widgets with all fields filled</p>
     * <p>Expected result</p>
     * <p>Widgets are created successfully</p>
     *
     * @dataProvider dataWidgetTypes
     * @depends createCategory
     * @test
     */
    public function createAllTypesOfWidgetsAllFields($dataWidgetType, $category)
    {
        $this->navigate('manage_cms_widgets');
        $temp = array();
        $temp['filter_sku'] = self::$products['sku']['simple'];
        $temp['category_path'] = $category;
        if ($dataWidgetType == 'catalog_category_link') {
            $nodes = explode('/', $category);
            $temp['title'] = $nodes[1];
        }
        if ($dataWidgetType == 'catalog_product_link') {
            $nodes = explode('/', $category);
            $temp['title'] = $nodes[1] . ' / ' . self::$products['name']['simple'];
        }
        $widgetData = $this->loadData($dataWidgetType . '_widget', $temp, 'widget_instance_title');
        $i = 1;
        foreach (self::$products['sku'] as $key => $value) {
            $widgetData['layout_updates']['layout_3']['choose_options']['product_' . $i++]['filter_sku'] = $value;
        }
        $i = 1;
        foreach (self::$products['sku'] as $key => $value) {
            $y = $i + 3;
            $widgetData['layout_updates']['layout_' . $y]['choose_options']['product_' . $i++]['filter_sku'] = $value;
        }
        $this->cmsWidgetsHelper()->createWidget($widgetData);
    }

    public function dataWidgetTypes()
    {
        return array(
            array('cms_page_link'),
            array('cms_static_block'),
            array('catalog_category_link'),
            array('catalog_new_products_list'),
            array('catalog_product_link'),
            array('orders_and_returns'),
            array('recently_compared_products'),
            array('recently_viewed_products')
        );
    }

    /**
     * <p>Creates All Types of widgets with required fields only</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Manage Widgets page</p>
     * <p>2. Create all types of widgets with required fields filled</p>
     * <p>Expected result</p>
     * <p>Widgets are created successfully</p>
     *
     * @dataProvider dataWidgetTypesReq
     * @depends createCategory
     * @test
     */
    public function createAllTypesOfWidgetsReqFields($dataWidgetType, $category)
    {
        $this->navigate('manage_cms_widgets');
        $temp = array();
        $temp['filter_sku'] = self::$products['sku']['simple'];
        $temp['category_path'] = $category;
        if ($dataWidgetType == 'catalog_category_link') {
            $nodes = explode('/', $category);
            $temp['title'] = $nodes[1];
        }
        if ($dataWidgetType == 'catalog_product_link') {
            $nodes = explode('/', $category);
            $temp['title'] = $nodes[1] . ' / ' . self::$products['name']['simple'];
        }
        $widgetData = $this->loadData($dataWidgetType . '_widget_req', $temp, 'widget_instance_title');
        $this->cmsWidgetsHelper()->createWidget($widgetData);


    }

    public function dataWidgetTypesReq()
    {
        return array(
            array('cms_page_link'),
            array('cms_static_block'),
            array('catalog_category_link'),
            array('catalog_new_products_list'),
            array('catalog_product_link'),
            array('orders_and_returns'),
            array('recently_compared_products'),
            array('recently_viewed_products')
        );
    }

    /**
     * <p>Creates All Types of widgets with required fields empty</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Manage Widgets page</p>
     * <p>2. Create all types of widgets with required fields empty</p>
     * <p>Expected result</p>
     * <p>Widgets are not created. Message about required field empty appears.</p>
     *
     * @dataProvider dataWidgetEmptyFields
     * @depends createCategory
     * @test
     */
    public function createWithEmptyFields($dataWidgetType, $emptyField, $fieldType, $category)
    {
        $this->navigate('manage_cms_widgets');
        $temp = array();
        $temp['filter_sku'] = self::$products['sku']['simple'];
        $temp['category_path'] = $category;
        if ($dataWidgetType == 'catalog_category_link') {
            $nodes = explode('/', $category);
            $temp['title'] = $nodes[1];
        }
        if ($dataWidgetType == 'catalog_product_link') {
            $nodes = explode('/', $category);
            $temp['title'] = $nodes[1] . ' / ' . self::$products['name']['simple'];
        }
        if ($fieldType == 'field') {
            $temp[$emptyField] = ' ';
        } elseif ($fieldType == 'dropdown') {
            if($emptyField == 'select_display_on') {
                $temp['select_block_reference'] = '%noValue%';
                $temp['select_template'] = '%noValue%';
            }
            $temp[$emptyField] = '-- Please Select --';
        } else {
            $temp['widget_options'] = '%noValue%';
            $this->addParameter('elementName', 'Not Selected');
        }
        $widgetData = $this->loadData($dataWidgetType . '_widget_req', $temp);
        $this->cmsWidgetsHelper()->createWidget($widgetData);
        $this->addFieldIdToMessage($fieldType, $emptyField);
        $this->assertTrue($this->validationMessage('empty_required_field'), $this->messages);
        $this->assertTrue($this->verifyMessagesCount(), $this->messages);
    }

    public function dataWidgetEmptyFields()
    {
        return array(
            array('cms_page_link', 'widget_instance_title', 'field'),
            array('cms_page_link', 'page_id', 'pageelement'),
            array('cms_page_link', 'select_display_on', 'dropdown'),
            array('cms_page_link', 'select_block_reference', 'dropdown'),
            array('cms_static_block', 'widget_instance_title', 'field'),
            array('cms_static_block', 'block_id', 'pageelement'),
            array('cms_static_block', 'select_display_on', 'dropdown'),
            array('cms_static_block', 'select_block_reference', 'dropdown'),
            array('catalog_category_link', 'widget_instance_title', 'field'),
            array('catalog_category_link', 'category_id', 'pageelement'),
            array('catalog_category_link', 'select_display_on', 'dropdown'),
            array('catalog_category_link', 'select_block_reference', 'dropdown'),
            array('catalog_new_products_list', 'widget_instance_title', 'field'),
            array('catalog_new_products_list', 'number_of_products_to_display', 'field'),
            array('catalog_new_products_list', 'select_display_on', 'dropdown'),
            array('catalog_new_products_list', 'select_block_reference', 'dropdown'),
            array('catalog_product_link', 'widget_instance_title', 'field'),
            array('catalog_product_link', 'category_id', 'pageelement'),
            array('catalog_product_link', 'select_display_on', 'dropdown'),
            array('catalog_product_link', 'select_block_reference', 'dropdown'),
            array('orders_and_returns', 'widget_instance_title', 'field'),
            array('orders_and_returns', 'select_display_on', 'dropdown'),
            array('orders_and_returns', 'select_block_reference', 'dropdown'),
            array('recently_compared_products', 'widget_instance_title', 'field'),
            array('recently_compared_products', 'number_of_products_to_display_compared_and_viewed', 'field'),
            array('recently_compared_products', 'select_display_on', 'dropdown'),
            array('recently_compared_products', 'select_block_reference', 'dropdown'),
            array('recently_viewed_products', 'widget_instance_title', 'field'),
            array('recently_viewed_products', 'number_of_products_to_display_compared_and_viewed', 'field'),
            array('recently_viewed_products', 'select_display_on', 'dropdown'),
            array('recently_viewed_products', 'select_block_reference', 'dropdown')
        );
    }

}
