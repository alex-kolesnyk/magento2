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
use Mtf\Client\Element\Locator;
use Magento\Catalog\Test\Block\Backend\ProductForm;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Related;

/**
 * Class CatalogProductEdit
 * Edit product page
 */
class CatalogProductEdit extends Page
{
    /**
     * URL for product creation
     */
    const MCA = 'catalog/product/edit';

    /*
     * Selector for message block
     *
     * @var string
     */
    protected $messagesSelector = '#messages.messages .messages';

    /**
     * Messages block
     *
     * @var \Magento\Core\Test\Block\Messages
     */
    protected $messagesBlock;

    /**
     * Product form block
     *
     * @var ProductForm
     */
    private $productFormBlock;

    /**
     * Catalog product grid on backend under "related products" tab when editing
     *
     * @var Related
     */
    private $relatedProductGrid;

    /**
     * Custom constructor
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_backend_url'] . self::MCA;

        $this->messagesBlock = Factory::getBlockFactory()->getMagentoCoreMessages(
            $this->_browser->find($this->messagesSelector, Locator::SELECTOR_CSS)
        );

        $this->productFormBlock = Factory::getBlockFactory()->getMagentoCatalogBackendProductForm(
            $this->_browser->find('body', Locator::SELECTOR_CSS)
        );

        $this->relatedProductGrid = Factory::getBlockFactory()->getMagentoCatalogAdminhtmlProductEditTabRelated(
            $this->_browser->find('related_product_grid', Locator::SELECTOR_ID)
        );
    }

    /**
     * Get messages block
     *
     * @return \Magento\Core\Test\Block\Messages
     */
    public function getMessagesBlock()
    {
        return $this->messagesBlock;
    }

    /**
     * Get product form block
     *
     * @return ProductForm
     */
    public function getProductBlockForm()
    {
        return $this->productFormBlock;
    }

    /**
     * Get the backend catalog product block
     *
     * @return Related
     */
    public function getRelatedProductGrid()
    {
        return $this->relatedProductGrid;
    }
}
