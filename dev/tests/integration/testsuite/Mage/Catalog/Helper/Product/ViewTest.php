<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

require Mage::getBaseDir() . '/app/code/core/Mage/Catalog/controllers/ProductController.php';

class Mage_Catalog_Helper_Product_ViewTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Helper_Product_View
     */
    protected $_helper;

    /**
     * @var Mage_Catalog_ProductController
     */
    protected $_controller;

    protected function setUp()
    {
        $this->_helper = new Mage_Catalog_Helper_Product_View;
        $request = new Magento_Test_Request();
        $request->setRouteName('catalog')
            ->setControllerName('product')
            ->setActionName('view');
        $this->_controller = new Mage_Catalog_ProductController($request, new Magento_Test_Response);
    }

    /**
     * Cleanup session, contaminated by product initialization methods
     */
    protected function tearDown()
    {
        Mage::getSingleton('Mage_Catalog_Model_Session')->unsLastViewedProductId();
        $this->_controller = null;
        $this->_helper = null;
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testInitProductLayout()
    {
        $uniqid = uniqid();
        $product = new Mage_Catalog_Model_Product;
        $product->setTypeId(Mage_Catalog_Model_Product_Type::DEFAULT_TYPE)->setId(99)->setUrlKey($uniqid);
        Mage::register('product', $product);

        $this->_helper->initProductLayout($product, $this->_controller);
        $rootBlock = $this->_controller->getLayout()->getBlock('root');
        $this->assertInstanceOf('Mage_Page_Block_Html', $rootBlock);
        $this->assertContains("product-{$uniqid}", $rootBlock->getBodyClass());
        $handles = $this->_controller->getLayout()->getUpdate()->getHandles();
        $this->assertContains('catalog_product_view_type_simple', $handles);
    }

    /**
     * @magentoDataFixture Mage/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     */
    public function testPrepareAndRender()
    {
        $this->_helper->prepareAndRender(10, $this->_controller);
        $this->assertNotEmpty($this->_controller->getResponse()->getBody());
        $this->assertEquals(10, Mage::getSingleton('Mage_Catalog_Model_Session')->getLastViewedProductId());
    }

    /**
     * @magentoDataFixture Mage/Catalog/_files/multiple_products.php
     * @expectedException Mage_Core_Exception
     * @magentoAppIsolation enabled
     */
    public function testPrepareAndRenderWrongController()
    {
        $controller = new Mage_Core_Controller_Front_Action(new Magento_Test_Request, new Magento_Test_Response);
        $this->_helper->prepareAndRender(10, $controller);
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testPrepareAndRenderWrongProduct()
    {
        $this->_helper->prepareAndRender(999, $this->_controller);
    }

    /**
     * Test for _getSessionMessageModels
     *
     * @magentoDataFixture Mage/Catalog/_files/two_products.php
     * @magentoAppIsolation enabled
     * @covers Mage_Catalog_Helper_Product_View::_getSessionMessageModels
     */
    public function testGetSessionMessageModels()
    {
        $expectedMessages = array(
            'Mage_Catalog_Model_Session'  => 'catalog message',
            'Mage_Checkout_Model_Session' => 'checkout message',
        );

        // add messages
        foreach ($expectedMessages as $sessionModel => $messageText) {
            /** @var $session Mage_Core_Model_Session_Abstract */
            $session = Mage::getSingleton($sessionModel);
            $session->addNotice($messageText);
        }

        // _getSessionMessageModels invokes inside prepareAndRender
        $this->_helper->prepareAndRender(10, $this->_controller);

        // assert messages
        $actualMessages = $this->_controller->getLayout()
            ->getMessagesBlock()
            ->getMessages();
        $this->assertSameSize($expectedMessages, $actualMessages);

        sort($expectedMessages);

        /** @var $message Mage_Core_Model_Message_Notice */
        foreach ($actualMessages as $key => $message) {
            $actualMessages[$key] = $message->getText();
        }
        sort($actualMessages);

        $this->assertEquals($expectedMessages, $actualMessages);
    }
}
