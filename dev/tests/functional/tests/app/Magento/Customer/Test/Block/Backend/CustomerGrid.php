<?php
/**
 * {license_notice}
 *
 * @spi
 * @category    Mtf
 * @package     Mtf
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Customer\Test\Block\Backend;

use Mtf\Client\Element\Locator;
use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Class CustomerGrid
 * Backend customer grid
 *
 * @package Magento\Customer\Test\Block\Backend
 */
class CustomerGrid extends Grid
{
    /**
     * Initialize block elements
     */
    protected function _init()
    {
        parent::_init();
        $this->editLink = '//td[contains(@class, "col-action")]//a';
        $this->filters = array(
            'email' => array(
                'selector' => '#customerGrid_filter_email'
            ),
        );
    }
}
