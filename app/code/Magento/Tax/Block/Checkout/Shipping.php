<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Block\Checkout;

/**
 * Subtotal Total Row Renderer
 */
class Shipping extends \Magento\Checkout\Block\Total\DefaultTotal
{
    /**
     * Template path
     *
     * @var string
     */
    protected $_template = 'checkout/shipping.phtml';

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $_taxConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    ) {
        $this->_taxConfig = $taxConfig;
        parent::__construct($context, $customerSession, $checkoutSession, $salesConfig, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Check if we need display shipping include and exclude tax
     *
     * @return bool
     */
    public function displayBoth()
    {
        return $this->_taxConfig->displayCartShippingBoth($this->getStore());
    }

    /**
     * Check if we need display shipping include tax
     *
     * @return bool
     */
    public function displayIncludeTax()
    {
        return $this->_taxConfig->displayCartShippingInclTax($this->getStore());
    }

    /**
     * Get shipping amount include tax
     *
     * @return float
     */
    public function getShippingIncludeTax()
    {
        return $this->getTotal()->getAddress()->getShippingInclTax();
    }

    /**
     * Get shipping amount exclude tax
     *
     * @return float
     */
    public function getShippingExcludeTax()
    {
        return $this->getTotal()->getAddress()->getShippingAmount();
    }

    /**
     * Get label for shipping include tax
     *
     * @return \Magento\Framework\Phrase
     */
    public function getIncludeTaxLabel()
    {
        return __(
            'Shipping Incl. Tax (%1)',
            $this->escapeHtml($this->getTotal()->getAddress()->getShippingDescription())
        );
    }

    /**
     * Get label for shipping exclude tax
     *
     * @return \Magento\Framework\Phrase
     */
    public function getExcludeTaxLabel()
    {
        return __(
            'Shipping Excl. Tax (%1)',
            $this->escapeHtml($this->getTotal()->getAddress()->getShippingDescription())
        );
    }
}
