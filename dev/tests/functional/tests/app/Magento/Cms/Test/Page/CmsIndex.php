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

namespace Magento\Cms\Test\Page;

use Mtf\Page\Page;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;

/**
 * Class CmsIndex
 * Home page for frontend
 *
 * @package Magento\Mage\Test\Page
 */
class CmsIndex extends Page
{
    /**
     * URL for home page
     */
    const MCA = 'cms/index/index';

    /**
     * Search block
     *
     * @var string
     */
    protected $searchBlock = '#search_mini_form';

    /**
     * Top menu navigation block
     *
     * @var string
     */
    protected $topmenuBlock = '[role=navigation]';

    /**
     * Page title block
     *
     * @var string
     */
    protected $titleBlock = '.page.title';

    /**
     * Footer block
     *
     * @var string
     */
    protected $footerBlock = 'footer.footer';

    /**
     * Page Top Links block
     *
     * @var string
     */
    protected $linksBlock = '.header .content .links';

    /**
     * Page Top Customer menu block
     *
     * @var string
     */
    protected $customerBlock = '.header .content .links';

    /**
     * Custom constructor
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_frontend_url'];
    }

    /**
     * Get the search block
     *
     * @return \Magento\Catalog\Test\Block\Search
     */
    public function getSearchBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogSearch(
            $this->_browser->find($this->searchBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get category title block
     *
     * @return \Magento\Page\Test\Block\Html\Topmenu
     */
    public function getTopmenu()
    {
        return Factory::getBlockFactory()->getMagentoPageHtmlTopmenu(
            $this->_browser->find($this->topmenuBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get title block
     *
     * @return \Magento\Page\Test\Block\Html\Title
     */
    public function getTitleBlock()
    {
        return Factory::getBlockFactory()->getMagentoPageHtmlTitle(
            $this->_browser->find($this->titleBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get footer block
     *
     * @return \Magento\Page\Test\Block\Html\Footer
     */
    public function getFooterBlock()
    {
        return Factory::getBlockFactory()->getMagentoPageHtmlFooter(
            $this->_browser->find($this->footerBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get Top Links block
     *
     * @return \Magento\Page\Test\Block\Links
     */
    public function getLinksBlock()
    {
        return Factory::getBlockFactory()->getMagentoPageLinks(
            $this->_browser->find($this->linksBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get customer menu block
     *
     * @return \Magento\Customer\Test\Block\Account\Customer
     */
    public function getCustomerMenuBlock()
    {
        return Factory::getBlockFactory()->getMagentoCustomerAccountCustomer(
            $this->_browser->find($this->customerBlock, Locator::SELECTOR_CSS)
        );
    }
}
