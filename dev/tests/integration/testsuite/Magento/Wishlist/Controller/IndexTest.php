<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Wishlist
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Wishlist\Controller;

class IndexTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Message\ManagerInterface
     */
    protected $_messages;

    protected function setUp()
    {
        parent::setUp();
        $logger = $this->getMock('Magento\Logger', array(), array(), '', false);
        $this->_customerSession = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Customer\Model\Session', array($logger));
        $this->_customerSession->login('customer@example.com', 'password');

        $this->_messages = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Message\ManagerInterface');

    }

    protected function tearDown()
    {
        $this->_customerSession->logout();
        $this->_customerSession = null;
        parent::tearDown();
    }

    /**
     * Verify wishlist view action
     *
     * The following is verified:
     * - \Magento\Wishlist\Model\Resource\Item\Collection
     * - \Magento\Wishlist\Block\Customer\Wishlist
     * - \Magento\Wishlist\Block\Customer\Wishlist\Items
     * - \Magento\Wishlist\Block\Customer\Wishlist\Item\Column
     * - \Magento\Wishlist\Block\Customer\Wishlist\Item\Column\Cart
     * - \Magento\Wishlist\Block\Customer\Wishlist\Item\Column\Comment
     * - \Magento\Wishlist\Block\Customer\Wishlist\Button
     * - that \Magento\Wishlist\Block\Customer\Wishlist\Item\Options doesn't throw a fatal error
     *
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     */
    public function testItemColumnBlock()
    {
        $this->dispatch('wishlist/index/index');
        $body = $this->getResponse()->getBody();
        $this->assertSelectCount('img[src~="small_image.jpg"][alt="Simple Product"]', 1, $body);
        $this->assertSelectCount('textarea[name~="description"]', 1, $body);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_xss.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testAddActionProductNameXss()
    {
        $this->dispatch('wishlist/index/add/product/1?nocookie=1');
        $messages = $this->_messages->getMessages()->getItems();
        $isProductNamePresent = false;
        foreach ($messages as $message) {
            if (strpos($message->getText(), '&lt;script&gt;alert(&quot;xss&quot;);&lt;/script&gt;') !== false) {
                $isProductNamePresent = true;
            }
            $this->assertNotContains('<script>alert("xss");</script>', (string)$message->getText());
        }
        $this->assertTrue($isProductNamePresent, 'Product name was not found in session messages');
    }

    /**
     * @magentoConfigFixture default_store cataloginventory/item_options/enable_qty_increments 1
     * @magentoConfigFixture default_store cataloginventory/item_options/qty_increments 5
     *
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_all_cart.php
     */
    public function testAllcartAction()
    {
        $this->dispatch('wishlist/index/allcart');

        /** @var \Magento\Checkout\Model\Cart $cart */
        $cart = $this->_objectManager->get('Magento\Checkout\Model\Cart');
        $quoteCount = $cart->getQuote()->getItemsCollection()->count();

        $this->assertEquals(0, $quoteCount);

        /** @var \Magento\Message\ManagerInterface $messageManager */
        $messageManager = $this->_objectManager->get('Magento\Message\ManagerInterface');

        $countErrors = $messageManager->getMessages()->getCountByType(\Magento\Message\MessageInterface::TYPE_ERROR);
        $this->assertEquals(1,$countErrors);
        $this->assertEquals(
            'You can buy this product only in increments of 5 for "Simple Product".',
            $messageManager->getMessages()->getLastAddedMessage()->getText()
        );
    }
}
