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

namespace Magento\Backend\Test\Block\Sales\Order;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Class Info
 * Order Payment Information block
 *
 * @package Magento\Backend
 */
class Info extends Block
{
    /**
     * 3D Secure Verification Result
     *
     * @var string
     */
    protected  $_verificationResult = '//tr[normalize-space(th)="3D Secure Verification Result:"]/td';

    /**
     * 3D Secure Cardholder Validation
     *
     * @var string
     */
    protected  $_cardholderValidation = '//tr[normalize-space(th)="3D Secure Cardholder Validation:"]/td';

    /**
     * 3D Secure Electronic Commerce Indicator
     *
     * @var string
     */
    protected  $_eCommerceIndicator = '//tr[normalize-space(th)="3D Secure Electronic Commerce Indicator:"]/td';

    /**
     * Get 3D Secure Verification Result
     *
     * @return array|string
     */
    public function getVerificationResult()
    {
        return $this->_rootElement->find($this->_verificationResult, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Get 3D Secure Verification Result
     *
     * @return array|string
     */
    public function getCardholderValidation()
    {
        return $this->_rootElement->find($this->_cardholderValidation, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Get 3D Secure Electronic Commerce Indicator
     *
     * @return array|string
     */
    public function getEcommerceIndicator()
    {
        return $this->_rootElement->find($this->_eCommerceIndicator, Locator::SELECTOR_XPATH)->getText();
    }
}
