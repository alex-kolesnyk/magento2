<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Catalog\Test\TestCase\Category;

use Mtf\Factory\Factory,
    Mtf\TestCase\Functional,
    Magento\Backend\Test\Fixture\Urlrewrite\Product;

/**
 * Class UrlrewriteTest
 * Product URL rewrite creation test
 *
 * @package Magento\Catalog\Test\TestCase\Product
 */
class UrlrewriteTest extends Functional
{
    /**
     * @ZephyrId MAGETWO-12409
     */
    public function testUrlRewriteCreation()
    {
        /** @var Product $urlRewriteProduct */
        $urlRewriteProduct = Factory::getFixtureFactory()->getMagentoBackendUrlrewriteProduct();
        $urlRewriteProduct->switchData('product_with_permanent_redirect');

        //Pages & Blocks
        $urlRewriteGridPage = Factory::getPageFactory()->getAdminUrlrewriteIndex();
        $pageActionsBlock = $urlRewriteGridPage->getPageActionsBlock();
        $urlRewriteEditPage = Factory::getPageFactory()->getAdminUrlrewriteEdit();
        $categoryTreeBlock = $urlRewriteEditPage->getCategoryTreeBlock();
        $productGridBlock = $urlRewriteEditPage->getProductGridBlock();
        $typeSelectorBlock = $urlRewriteEditPage->getUrlRewriteTypeSelectorBlock();
        $urlRewriteInfoForm = $urlRewriteEditPage->getUrlRewriteInformationForm();

        //Steps
        Factory::getApp()->magentoBackendLoginUser();
        $urlRewriteGridPage->open();
        $pageActionsBlock->clickAddNew();
        $typeSelectorBlock->selectType('For product');
        $productGridBlock->searchAndSelect(array('id' => $urlRewriteProduct->getProductId()));
        $categoryTreeBlock->selectCategory($urlRewriteProduct->getCategoryName());
        $urlRewriteInfoForm->fill($urlRewriteProduct);
        $urlRewriteInfoForm->save();
        $this->assertContains(
            'The URL Rewrite has been saved.',
            $urlRewriteGridPage->getMessagesBlock()->getSuccessMessages()
        );

        $this->assertUrlRedirect(
            $_ENV['app_frontend_url'] . $urlRewriteProduct->getRewrittenRequestPath(),
            $_ENV['app_frontend_url'] . $urlRewriteProduct->getOriginalRequestPath()
        );
    }

    /**
     * Assert that request URL redirects to target URL
     *
     * @param string $requestUrl
     * @param string $targetUrl
     * @param string $message
     */
    protected function assertUrlRedirect($requestUrl, $targetUrl, $message = '')
    {
        $browser = Factory::getClientBrowser();
        $browser->open($requestUrl);
        $this->assertStringStartsWith($targetUrl, $browser->getUrl(), $message);
    }
}
