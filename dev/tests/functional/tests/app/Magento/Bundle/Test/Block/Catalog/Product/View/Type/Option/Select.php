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

namespace Magento\Bundle\Test\Block\Catalog\Product\View\Type\Option;

use Mtf\Block\Form;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class Select
 * Bundle option dropdown type
 *
 * @package Magento\Bundle\Test\Block\Catalog\Product\View\Type\Option
 */
class Select extends Form
{
    /**
     * Initialize block elements
     */
    protected function _init()
    {
        $this->_mapping = array(
            'value' => 'select',
            'qty' => 'input.qty'
        );
    }

    /**
     * Set data in bundle option
     *
     * @param array $data
     */
    public function fillOption(array $data)
    {
        $this->waitForElementVisible($this->_mapping['value']);
        $this->waitForElementVisible($this->_mapping['qty']);

        $select = $this->_rootElement->find($this->_mapping['value'], Locator::SELECTOR_CSS, 'select');
        $select->setValue($data['value']);
        $qtyField = $this->_rootElement->find($this->_mapping['qty'], Locator::SELECTOR_CSS);
        if (!$qtyField->isDisabled()) {
            $qtyField->setValue($data['qty']);
        }
    }
}
