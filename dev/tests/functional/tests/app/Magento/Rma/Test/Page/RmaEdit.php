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

namespace Magento\Rma\Test\Page;

use Mtf\Page\Page;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;

/**
 * Class Returns
 * View returns page
 *
 * @package Magento\Rma\Test\Page
 */
class RmaEdit extends Page
{

    /**
     * URL for RMA Edit page
     */
    const MCA = 'rma/edit';

    /**
     * Rma edit tabs block
     *
     * @var string
     */
    protected $formTabsBlock = '#rma_info_tabs';

    /**
     * Rma actions block
     *
     * @var string
     */
    protected $rmaActionsBlock = '.page-actions';


    /**
     * Custom constructor
     */
    protected function _init()
    {
        $this->_url = $this->_url = $_ENV['app_backend_url'] . self::MCA;
    }

    /**
     * Get Rma info tabs block
     *
     * @return \Magento\Backend\Test\Block\Widget\FormTabs
     */
    public function getFormTabsBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendWidgetFormTabs(
            $this->_browser->find($this->formTabsBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get Rma actions block
     *
     * @return \Magento\Rma\Test\Block\Adminhtml\Rma\Actions
     */
    public function getRmaActionsBlock()
    {
        return Factory::getBlockFactory()->getMagentoRmaAdminhtmlRmaActions(
            $this->_browser->find($this->rmaActionsBlock, Locator::SELECTOR_CSS)
        );
    }
}
