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

namespace Magento\Catalog\Test\Page\Product;

use Mtf\Page\Page;
use Mtf\Factory\Factory;
use Mtf\Fixture\DataFixture;
use Mtf\Client\Element\Locator;

/**
 * Class CatalogProductNew
 * Create product page
 *
 * @package Magento\Catalog\Test\Page\Product
 */
class CatalogProductNew extends Page
{
    /**
     * URL for product creation
     */
    const MCA = 'catalog/product/new';

    /**
     * New attribute selector
     *
     * @var string
     */
    protected $newAttribute = 'body';

    /**
     * New attribute frame selector
     *
     * @var string
     */
    protected $newAttributeFrame = '#create_new_attribute_container';

    /**
     * Product form block
     *
     * @var string
     */
    protected $productFormBlock = 'body';

    /**
     * Global messages block
     *
     * @var string
     */
    protected $messagesBlock = '#messages .messages';

    /**
     * Selector for message block
     *
     * @var string
     */
    protected $messagesSelector = '#messages.messages .messages';

    /**
     * Custom constructor
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_backend_url'] . static::MCA;
    }

    /**
     * @param DataFixture $fixture
     */
    public function init(DataFixture $fixture)
    {
        $dataConfig = $fixture->getDataConfig();

        $params = isset($dataConfig['create_url_params']) ? $dataConfig['create_url_params'] : array();
        foreach ($params as $paramName => $paramValue) {
            $this->_url .= '/' . $paramName . '/' . $paramValue;
        }
    }

    /**
     * Get product form block
     *
     * @return \Magento\Catalog\Test\Block\Backend\ProductForm
     */
    public function getProductBlockForm()
    {
        return Factory::getBlockFactory()->getMagentoCatalogBackendProductForm(
            $this->_browser->find($this->productFormBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get global messages block
     *
     * @return \Magento\Core\Test\Block\Messages
     */
    public function getMessagesBlock()
    {
        return Factory::getBlockFactory()->getMagentoCoreMessages(
            $this->_browser->find($this->messagesBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get attribute edit block
     *
     * @return \Magento\Catalog\Test\Block\Backend\Product\Attribute\Edit
     */
    public function getAttributeEditBlock()
    {
        $this->_browser->switchToFrame(new Locator($this->newAttributeFrame));
        return Factory::getBlockFactory()->getMagentoCatalogBackendProductAttributeEdit(
            $this->_browser->find($this->newAttribute, Locator::SELECTOR_TAG_NAME)
        );
    }

    /**
     * Switch back to main page from iframe
     */
    public function switchToMainPage()
    {
        $this->_browser->switchToFrame();
    }

    /**
     * Get upsell block
     *
     * @return \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Upsell
     */
    public function getUpsellBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogAdminhtmlProductEditTabUpsell(
            $this->_browser->find('up_sell_product_grid', Locator::SELECTOR_ID)
        );
    }

    /**
     * Get the backend catalog product block
     *
     * @return \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Related
     */
    public function getRelatedProductGrid()
    {
        return Factory::getBlockFactory()->getMagentoCatalogAdminhtmlProductEditTabRelated(
            $this->_browser->find('related_product_grid', Locator::SELECTOR_ID)
        );
    }
}
