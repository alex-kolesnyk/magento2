<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Adminhtml\Block\Catalog\Product\Options;

/**
 * @magentoAppArea adminhtml
 */
class AjaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Adminhtml\Block\Catalog\Product\Options\Ajax
     */
    protected $_block = null;

    protected function setUp()
    {
        parent::setUp();
        $this->_block = \Mage::app()->getLayout()->createBlock('Magento\Adminhtml\Block\Catalog\Product\Options\Ajax');
    }

    public function testToHtmlWithoutProducts()
    {
        $this->assertEquals(json_encode(array()), $this->_block->toHtml());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_with_options.php
     */
    public function testToHtml()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Core\Model\Registry')->register('import_option_products', array(1));
        $result = json_decode($this->_block->toHtml(), true);
        $this->assertEquals('test_option_code_1', $result[0]['title']);
    }
}
