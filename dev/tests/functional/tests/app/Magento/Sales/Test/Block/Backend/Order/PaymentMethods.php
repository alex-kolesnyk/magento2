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

namespace Magento\Sales\Test\Block\Backend\Order;

use Magento\Backend\Test\Block\Template;
use Magento\Sales\Test\Fixture\Order;
use Mtf\Block\Block;
use Mtf\Client\Element\Locator;
use Magento\Payment\Test\Block\Form;
use Mtf\Factory\Factory;

/**
 * Class Methods
 * Order creation in backend payment methods
 *
 * @package Magento\Sales\Test\Block\Backend\Order
 */
class PaymentMethods extends Block
{
    /**
     * @var Template
     */
    protected $_templateBlock;

    /**
     * @inheritdoc
     */
    protected function _init()
    {
        $this->_templateBlock = Factory::getBlockFactory()->getMagentoBackendTemplate(
            $this->_rootElement->find('./ancestor::body', Locator::SELECTOR_XPATH)
        );
    }

    /**
     * Select payment method
     *
     * @param Order $fixture
     */
    public function selectPaymentMethod(Order $fixture)
    {
        $payment = $fixture->getPaymentMethod();
        $paymentCode = $payment->getPaymentCode();
        $this->_rootElement->find('#p_method_' . $paymentCode, Locator::SELECTOR_CSS)->click();
        $this->_templateBlock->waitLoader();
    }
}
