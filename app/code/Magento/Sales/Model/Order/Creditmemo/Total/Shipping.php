<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Order creditmemo shipping total calculation model
 */
class Magento_Sales_Model_Order_Creditmemo_Total_Shipping extends Magento_Sales_Model_Order_Creditmemo_Total_Abstract
{
    /**
     * @var Magento_Core_Model_StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Magento_Tax_Model_Config
     */
    protected $_taxConfig;

    /**
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     * @param Magento_Tax_Model_Config $taxConfig
     * @param array $data
     */
    public function __construct(
        Magento_Core_Model_StoreManagerInterface $storeManager,
        Magento_Tax_Model_Config $taxConfig,
        array $data = array()
    ) {
        parent::__construct($data);
        $this->_storeManager = $storeManager;
        $this->_taxConfig = $taxConfig;
    }

    public function collect(Magento_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $allowedAmount          = $order->getShippingAmount()-$order->getShippingRefunded();
        $baseAllowedAmount      = $order->getBaseShippingAmount()-$order->getBaseShippingRefunded();

        $shipping               = $order->getShippingAmount();
        $baseShipping           = $order->getBaseShippingAmount();
        $shippingInclTax        = $order->getShippingInclTax();
        $baseShippingInclTax    = $order->getBaseShippingInclTax();

        $isShippingInclTax = $this->_taxConfig->displaySalesShippingInclTax($order->getStoreId());

        /**
         * Check if shipping amount was specified (from invoice or another source).
         * Using has magic method to allow setting 0 as shipping amount.
         */
        if ($creditmemo->hasBaseShippingAmount()) {
            $baseShippingAmount = $this->_storeManager->getStore()->roundPrice($creditmemo->getBaseShippingAmount());
            if ($isShippingInclTax && $baseShippingInclTax != 0) {
                $part = $baseShippingAmount/$baseShippingInclTax;
                $shippingInclTax    = $this->_storeManager->getStore()->roundPrice($shippingInclTax*$part);
                $baseShippingInclTax= $baseShippingAmount;
                $baseShippingAmount = $this->_storeManager->getStore()->roundPrice($baseShipping*$part);
            }
            /*
             * Rounded allowed shipping refund amount is the highest acceptable shipping refund amount.
             * Shipping refund amount shouldn't cause errors, if it doesn't exceed that limit.
             * Note: ($x < $y + 0.0001) means ($x <= $y) for floats
             */
            if ($baseShippingAmount < $this->_storeManager->getStore()->roundPrice($baseAllowedAmount) + 0.0001) {
                /*
                 * Shipping refund amount should be equated to allowed refund amount,
                 * if it exceeds that limit.
                 * Note: ($x > $y - 0.0001) means ($x >= $y) for floats
                 */
                if ($baseShippingAmount > $baseAllowedAmount - 0.0001) {
                    $shipping     = $allowedAmount;
                    $baseShipping = $baseAllowedAmount;
                } else {
                    if ($baseShipping != 0) {
                        $shipping = $shipping * $baseShippingAmount / $baseShipping;
                    }
                    $shipping     = $this->_storeManager->getStore()->roundPrice($shipping);
                    $baseShipping = $baseShippingAmount;
                }
            } else {
                $baseAllowedAmount = $order->getBaseCurrency()->format($baseAllowedAmount,null,false);
                throw new Magento_Core_Exception(
                    __('Maximum shipping amount allowed to refund is: %1', $baseAllowedAmount)
                );
            }
        } else {
            if ($baseShipping != 0) {
                $allowedTaxAmount = $order->getShippingTaxAmount() - $order->getShippingTaxRefunded();
                $baseAllowedTaxAmount = $order->getBaseShippingTaxAmount() - $order->getBaseShippingTaxRefunded();

                $shippingInclTax = $this->_storeManager->getStore()->roundPrice($allowedAmount + $allowedTaxAmount);
                $baseShippingInclTax = $this->_storeManager->getStore()
                    ->roundPrice($baseAllowedAmount + $baseAllowedTaxAmount);
            }
            $shipping           = $allowedAmount;
            $baseShipping       = $baseAllowedAmount;
        }

        $creditmemo->setShippingAmount($shipping);
        $creditmemo->setBaseShippingAmount($baseShipping);
        $creditmemo->setShippingInclTax($shippingInclTax);
        $creditmemo->setBaseShippingInclTax($baseShippingInclTax);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $shipping);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseShipping);
        return $this;
    }
}
