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
 * Url Rewrite Admin Page
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Community2_Mage_UrlRewrite_CreateTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
    }
    protected function tearDownAfterTest()
    {
        $windowQty = $this->getAllWindowNames();
        if (count($windowQty) > 1 && end($windowQty) != 'null') {
            $this->selectWindow("name=" . end($windowQty));
            $this->close();
            $this->selectWindow(null);
        }
    }

    /**
     * <p>Verifying Required field for Custom URL rewrite</p>
     * <p>Steps</p>
     * <p>1. Go to URL rewrite managment</p>
     * <p>2. Click Add URL rewrite button</p>
     * <p>3. At Create URL rewrite dropdown select Custom</p>
     * <p>4. Click Save button</p>
     * <p>Expected result:</p>
     * <p>Custom URL rewrite doesn't created</p>
     * <p>Message "This is a required field." is displayed</p>
     *
     * @param string $emptyField
     * @param string $messageCount
     *
     * @test
     * @dataProvider withRequiredFieldsEmptyDataProvider
     * @TestlinkId TL-MAGE-5518
     */
    public function withRequiredFieldsEmpty($emptyField, $messageCount)
    {
        $fieldData = $this->loadDataSet('UrlRewrite', 'url_rewrite_custom', array($emptyField => '%noValue%'));
        //Steps
        $this->navigate('manage_urlrewrites');
        $this->clickButton('add_new_rewrite','true');
        $this->fillDropdown('create_url_rewrite_dropdown','Custom');
        $this->waitForPageToLoad();
        $this->validatePage();
        $this->fillForm($fieldData);
        $this->clickButton('save', false);
        //Verifying
        $xpath = $this->_getControlXpath('field', $emptyField);
        $this->addParameter('fieldXpath', $xpath);
        $this->assertMessagePresent('error', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount($messageCount), $this->getParsedMessages());
    }

    public function withRequiredFieldsEmptyDataProvider ()
    {
        return array (
            array ('id_path', 1),
            array ('request_path', 1),
            array ('target_path', 1)
        );
    }

    /**
     * <p>Verifying Required field for Product URl rewrite</p>
     * <p>Steps</p>
     * <p>1. Go to URL rewrite managment</p>
     * <p>2. Click Add URL rewrite button</p>
     * <p>3. Create URL Rewrite = "For product"</p>
     * <p>4. Select Product in Grid</p>
     * <p>5. Select Category</p>
     * <p>Expected result:</p>
     * <p>"ID Path" & "Target Path" won't editable</p>
     *
     * @test
     * @TestlinkId TL-MAGE-5517
     */
    public function withRequiredFieldsNotEditable()
    {
        //Create Simple Product
        $this->navigate('manage_products');
        $productData = $this->loadDataSet('UrlRewrite', 'simple_product_required');
        $productSearch =
            $this->loadDataSet('Product', 'product_search', array('product_sku' => $productData['general_sku']));
        $this->productHelper()->createProduct($productData);

        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');

        //Open Manage URL rewrite page
        $this->admin('manage_urlrewrites');

        //Click 'Add new rewrite' button
        $this->clickButton('add_new_rewrite',true);
        $this->waitForAjax();

        //Select "For Product"
        $this->fillDropdown('create_url_rewrite_dropdown', 'For product');
        $this->waitForPageToLoad();

        //Find product in the Grid and open it
        $this->validatePage('add_new_urlrewrite_product');
        $this->searchAndOpen($productSearch,false,'product_rewrite');
        $this->waitForPageToLoad();
        $this->addParameter('id', $this->defineParameterFromUrl('product'));
        $this->validatePage();

        //Select Category
        $categorySearch = $productData['categories'];
        $this->addParameter('rootName', $categorySearch);
        $this->clickControl('link', 'root_category', false);
        $this->waitForPageToLoad();
        $this->addParameter('categoryId', $this->defineParameterFromUrl('category'));
        $this->validatePage();

        //Check fields id_path & target path isn't editable
        if ($this->isEditable('id_path'))
        {
            throw new PHPUnit_Framework_Exception('ID Path field is editable!');
        }
        if ($this->isEditable('target_path'))
        {
            throw new PHPUnit_Framework_Exception('Target Path field is editable!');
        }
    }
}
