<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Adminhtml
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Adminhtml_Catalog_ProductControllerTest extends Mage_Backend_Utility_Controller
{
    /**
     * @magentoDataFixture Mage/Catalog/_files/product_configurable.php
     */
    public function testSaveActionAssociatedProductIds()
    {
        $associatedProductIds = array(3, 14, 15, 92);
        $this->getRequest()->setPost(array(
            'attributes' => array($this->_getConfigurableAttribute()->getId()),
            'associated_product_ids' => $associatedProductIds,
        ));

        $this->dispatch('backend/admin/catalog_product/save');

        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::registry('current_product');
        $this->assertEquals($associatedProductIds, $product->getAssociatedProductIds());

        /** @see Mage_Backend_Utility_Controller::assertPostConditions() */
        $this->markTestIncomplete('Suppressing admin error messages validation until the bug MAGETWO-7044 is fixed.');
    }

    /**
     * Retrieve configurable attribute instance
     *
     * @return Mage_Catalog_Model_Entity_Attribute
     */
    protected function _getConfigurableAttribute()
    {
        return Mage::getModel('Mage_Catalog_Model_Entity_Attribute')->loadByCode(
            Mage::getSingleton('Mage_Eav_Model_Config')->getEntityType('catalog_product')->getId(),
            'test_configurable'
        );
    }

    public function testSaveActionWithDangerRequest()
    {
        $this->getRequest()->setPost(array(
            'product' => array(
                'entity_id' => 15
            ),
        ));
        $this->dispatch('backend/admin/catalog_product/save');
        $this->assertSessionMessages(
            $this->equalTo(array('Unable to save product')), Mage_Core_Model_Message::ERROR
        );
        $this->assertRedirect($this->stringContains('/backend/admin/catalog_product/edit'));
    }

    /**
     * @magentoDataFixture Mage/Catalog/_files/product_simple.php
     */
    public function testSaveActionAndNew()
    {
        $this->getRequest()->setPost(array('back' => 'new'));
        $this->dispatch('backend/admin/catalog_product/save/id/1');
        $this->assertRedirect($this->stringStartsWith('http://localhost/index.php/backend/admin/catalog_product/new/'));
        $this->assertSessionMessages(
            $this->contains('The product has been saved.'), Mage_Core_Model_Message::SUCCESS
        );
    }

    /**
     * @magentoConfigFixture limitations/catalog_product 1
     * @magentoDataFixture Mage/Catalog/_files/product_simple.php
     */
    public function testSaveActionAndNewLimitationReached()
    {
        $this->getRequest()->setPost(array('back' => 'new'));
        $this->dispatch('backend/admin/catalog_product/save/id/1');
        $this->assertRedirect(
            $this->stringStartsWith('http://localhost/index.php/backend/admin/catalog_product/edit/id/1')
        );
        $this->assertSessionMessages(
            $this->contains('The product has been saved.'), Mage_Core_Model_Message::SUCCESS
        );
        $this->assertSessionMessages($this->contains("You can't create new product."), Mage_Core_Model_Message::ERROR);
    }

    /**
     * @magentoDataFixture Mage/Catalog/_files/product_simple.php
     */
    public function testSaveActionAndDuplicate()
    {
        $this->getRequest()->setPost(array('back' => 'duplicate'));
        $this->dispatch('backend/admin/catalog_product/save/id/1');
        $this->assertRedirect(
            $this->stringStartsWith('http://localhost/index.php/backend/admin/catalog_product/edit/')
        );
        $this->assertRedirect($this->logicalNot(
            $this->stringStartsWith('http://localhost/index.php/backend/admin/catalog_product/edit/id/1')
        ));
        $this->assertSessionMessages(
            $this->contains('The product has been saved.'), Mage_Core_Model_Message::SUCCESS
        );
        $this->assertSessionMessages(
            $this->contains('The product has been duplicated.'), Mage_Core_Model_Message::SUCCESS
        );
    }

    /**
     * @magentoConfigFixture limitations/catalog_product 1
     * @magentoDataFixture Mage/Catalog/_files/product_simple.php
     */
    public function testSaveActionAndDuplicateLimitationReached()
    {
        $this->getRequest()->setPost(array('back' => 'duplicate'));
        $this->dispatch('backend/admin/catalog_product/save/id/1');
        $this->assertRedirect(
            $this->stringStartsWith('http://localhost/index.php/backend/admin/catalog_product/edit/id/1')
        );
        $this->assertSessionMessages(
            $this->contains('The product has been saved.'), Mage_Core_Model_Message::SUCCESS
        );
        $this->assertSessionMessages($this->contains("You can't create new product."), Mage_Core_Model_Message::ERROR);
    }

    public function testIndexAction()
    {
        $this->dispatch('backend/admin/catalog_product');
        $body = $this->getResponse()->getBody();

        $this->assertNotContains($this->_getCreateRestrictedMessage(), $body);

        $this->assertSelectCount('#add_new_product', 1, $body,
            '"Add Product" button container should be present on Manage Products page, if the limit is not  reached');
        $this->assertSelectCount('#add_new_product-button', 1, $body,
            '"Add Product" button should be present on Manage Products page, if the limit is not reached');
        $this->assertSelectCount('#add_new_product-button.disabled', 0, $body,
            '"Add Product" button should be enabled on Manage Products page, if the limit is not reached');
        $this->assertSelectCount('#add_new_product .action-toggle', 1, $body,
            '"Add Product" button split should be present on Manage Products page, if the limit is not reached');
    }

    /**
     * Return the expected message, used by product limitation
     *
     * @return string
     */
    protected function _getCreateRestrictedMessage()
    {
        /** @var Mage_Catalog_Model_Product_Limitation $limitation */
        $limitation = Mage::getModel('Mage_Catalog_Model_Product_Limitation');
        return $limitation->getCreateRestrictedMessage();
    }

    /**
     * @magentoConfigFixture limitations/catalog_product 1
     * @magentoDataFixture Mage/Catalog/_files/product_simple.php
     */
    public function testIndexActionLimited()
    {
        $this->dispatch('backend/admin/catalog_product');
        $body = $this->getResponse()->getBody();

        $this->assertContains($this->_getCreateRestrictedMessage(), $body);

        $this->assertSelectCount('#add_new_product', 1, $body,
            '"Add Product" button container should be present on Manage Products page, if the limit is reached');
        $this->assertSelectCount('#add_new_product-button.disabled', 1, $body,
            '"Add Product" button should be present and disabled on Manage Products page, if the limit is reached');
        $this->assertSelectCount('#add_new_product .action-toggle', 0, $body,
            '"Add Product" button split should not be present on Manage Products page, if the limit is reached');
    }

    /**
     * @magentoDataFixture Mage/Catalog/_files/product_simple.php
     */
    public function testEditAction()
    {
        $this->dispatch('backend/admin/catalog_product/edit/id/1');
        $body = $this->getResponse()->getBody();
        $this->assertNotContains($this->_getCreateRestrictedMessage(), $body);
        $this->assertSelectCount('#save-split-button', 1, $body,
            '"Save" button isn\'t present on Edit Product page');
        $this->assertSelectCount('#save-split-button-new-button', 1, $body,
            '"Save & New" button isn\'t present on Edit Product page');
        $this->assertSelectCount('#save-split-button-duplicate-button', 1, $body,
            '"Save & Duplicate" button isn\'t present on Edit Product page');
        $this->assertNotContains('Maximum allowed number of categories is reached.', $body,
            'New category creation should not be restricted on Edit Product page');
    }

    /**
     * @magentoConfigFixture limitations/catalog_product 1
     * @magentoConfigFixture limitations/catalog_category 1
     * @magentoDataFixture Mage/Catalog/_files/product_simple.php
     */
    public function testEditActionLimited()
    {
        $this->dispatch('backend/admin/catalog_product/edit/id/1');
        $body = $this->getResponse()->getBody();
        $this->assertContains($this->_getCreateRestrictedMessage(), $body);
        $this->assertSelectCount('#save-split-button', 1, $body,
            '"Save" button isn\'t present on Edit Product page');
        $this->assertSelectCount('#save-split-button-new-button', 0, $body,
            '"Save & New" button should not be present on Edit Product page, if the limit is reached');
        $this->assertSelectCount('#save-split-button-duplicate-button', 0, $body,
            '"Save & Duplicate" should not be present on Edit Product page, if the limit is reached');
        $this->assertContains('Maximum allowed number of categories is reached.', $body,
            'New category creation should be restricted on Edit Product page, if the limit is reached');
    }

    /**
     * @magentoConfigFixture limitations/catalog_product 2
     * @magentoDataFixture Mage/Catalog/_files/product_simple.php
     */
    public function testEditActionAllowedNewProduct()
    {
        $this->dispatch('backend/admin/catalog_product/edit/id/1');
        $body = $this->getResponse()->getBody();
        $this->assertSelectCount('#save-split-button', 1, $body,
            '"Save" button isn\'t present on Edit Product page');
        $this->assertSelectCount('#save-split-button-new-button', 1, $body,
            '"Save & New" button isn\'t present on Edit Product page');
        $this->assertSelectCount('#save-split-button-duplicate-button', 1, $body,
            '"Save & Duplicate" isn\'t present on Edit Product page');
    }

    /**
     * @magentoConfigFixture limitations/catalog_category 2
     * @magentoDataFixture Mage/Catalog/_files/product_simple.php
     */
    public function testEditActionAllowedNewCategory()
    {
        $this->dispatch('backend/admin/catalog_product/edit/id/1');
        $body = $this->getResponse()->getBody();
        $this->assertNotContains('Maximum allowed number of categories is reached.', $body,
            'New category creation should not be restricted on Edit Product page');
    }

    /**
     * @magentoConfigFixture limitations/catalog_product 2
     */
    public function testNewActionAllowedNewProduct()
    {
        $attributeSetId = $this->_getDefaultAttributeSetId();
        $this->dispatch("backend/admin/catalog_product/new/set/$attributeSetId/type/simple");
        $body = $this->getResponse()->getBody();
        $this->assertSelectCount('#save-split-button', 1, $body,
            '"Save" button isn\'t present on New Product page');
        $this->assertSelectCount('#save-split-button-new-button', 1, $body,
            '"Save & New" button isn\'t present on New Product page');
        $this->assertSelectCount('#save-split-button-duplicate-button', 1, $body,
            '"Save & Duplicate" button isn\'t present on New Product page');
    }

    /**
     * @magentoConfigFixture limitations/catalog_product 1
     */
    public function testNewActionRestrictedNewProduct()
    {
        $this->dispatch('backend/admin/catalog_product/new/set/4/type/simple');
        $body = $this->getResponse()->getBody();
        $this->assertSelectCount('#save-split-button', 1, $body,
            '"Save" button isn\'t present on New Product page');
        $this->assertSelectCount('#save-split-button-new-button', 0, $body,
            '"Save & New" button should not be present on New Product page, if last allowed product is being created');
        $this->assertSelectCount('#save-split-button-duplicate-button', 0, $body,
            '"Save & Duplicate" should not be present on New Product page, if last allowed product is being created');
    }

    /**
     * Get ID of default product attribute set
     *
     * @return int
     */
    protected function _getDefaultAttributeSetId()
    {
        /** @var $installer Mage_Catalog_Model_Resource_Setup */
        $installer = Mage::getResourceModel(
            'Mage_Catalog_Model_Resource_Setup',
            array('resourceName' => 'catalog_setup')
        );
        return $installer->getDefaultAttributeSetId('catalog_product');
    }

    /**
     * @magentoConfigFixture limitations/catalog_product 2
     * @magentoDataFixture Mage/Catalog/_files/product_simple.php
     * @dataProvider validateActionOnVariationsLimitReachedDataProvider
     */
    public function testValidateActionOnVariationsLimitReached($productId, $expectedMessage)
    {
        $productData = array();
        $variationsMatrix = array(
            array('configurable_attribute' => '{"size":1}'),
            array('configurable_attribute' => '{"size":1}'),
        );
        $this->getRequest()
            ->setPost('product', $productData)
            ->setPost('id', $productId)
            ->setPost('variations-matrix', $variationsMatrix);

        $this->dispatch('backend/admin/catalog_product/validate');

        $this->assertContains($expectedMessage, $this->getResponse()->getBody());
    }

    public static function validateActionOnVariationsLimitReachedDataProvider()
    {
        // @codingStandardsIgnoreStart
        $message = 'We could not save the product. You tried to add %d products, but the most you can have is %d. To add more, please upgrade your service.';
        // @codingStandardsIgnoreEnd
        return array(
            'new product' => array(
                null,
                sprintf($message, 3, 2),
            ),
            'existing product' => array(
                1,
                sprintf($message, 2, 2),
            ),
        );
    }
}
