<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_GoogleCheckout
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Google Checkout shortcut link
 *
 * @category   Magento
 * @package    Magento_GoogleCheckout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GoogleCheckout\Block;

class Link extends \Magento\View\Block\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\GoogleCheckout\Model\PaymentFactory
     */
    protected $paymentFactory;

    /**
     * @param \Magento\GoogleCheckout\Model\PaymentFactory $paymentFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\View\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\GoogleCheckout\Model\PaymentFactory $paymentFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Core\Helper\Data $coreData,
        \Magento\View\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->paymentFactory = $paymentFactory;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($coreData, $context, $data);
    }

    public function getImageStyle()
    {
        $s = $this->_storeConfig->getConfig('google/checkout/checkout_image');
        if (!$s) {
            $s = '180/46/trans';
        }
        return explode('/', $s);
    }

    public function getImageUrl()
    {
        $url = 'https://checkout.google.com/buttons/checkout.gif';
        $url .= '?merchant_id='.$this->_storeConfig->getConfig('google/checkout/merchant_id');
        $v = $this->getImageStyle();
        $url .= '&w='.$v[0].'&h='.$v[1].'&style='.$v[2];
        $url .= '&variant='.($this->getIsDisabled() ? 'disabled' : 'text');
        $url .= '&loc='.$this->_storeConfig->getConfig('google/checkout/locale');
        return $url;
    }

    public function getCheckoutUrl()
    {
        return $this->getUrl('googlecheckout/redirect/checkout');
    }

    public function getImageWidth()
    {
         $v = $this->getImageStyle();
         return $v[0];
    }

    public function getImageHeight()
    {
         $v = $this->getImageStyle();
         return $v[1];
    }

    /**
     * Check whether method is available and render HTML
     * @return string
     */
    public function _toHtml()
    {
        $quote = $this->checkoutSession->getQuote();
        if ($this->paymentFactory->create()->isAvailable($quote) && $quote->validateMinimumAmount()) {
            $this->_eventManager->dispatch('googlecheckout_block_link_html_before', array('block' => $this));
            return parent::_toHtml();
        }
        return '';
    }

    public function getIsDisabled()
    {
        $quote = $this->checkoutSession->getQuote();
        /* @var $quote \Magento\Sales\Model\Quote */
        foreach ($quote->getAllVisibleItems() as $item) {
            /* @var $item \Magento\Sales\Model\Quote\Item */
            if (!$item->getProduct()->getEnableGooglecheckout()) {
                return true;
            }
        }
        return false;
    }
}
