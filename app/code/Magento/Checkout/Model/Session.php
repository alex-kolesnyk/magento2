<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Checkout
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Checkout\Model;

class Session extends \Magento\Session\SessionManager
{
    /**
     * Checkout state begin
     */
    const CHECKOUT_STATE_BEGIN = 'begin';

    /**
     * Quote instance
     *
     * @var \Magento\Sales\Model\Quote
     */
    protected $_quote;

    /**
     * Customer instance
     *
     * @var null|\Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * Whether load only active quote
     *
     * @var bool
     */
    protected $_loadInactive = false;

    /**
     * Loaded order instance
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var \Magento\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\Session\SidResolverInterface $sidResolver
     * @param \Magento\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Session\ValidatorInterface $validator
     * @param \Magento\Session\StorageInterface $storage
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\QuoteFactory $quoteFactory
     * @param \Magento\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param null $sessionName
     */
    public function __construct(
        \Magento\App\RequestInterface $request,
        \Magento\Session\SidResolverInterface $sidResolver,
        \Magento\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Session\SaveHandlerInterface $saveHandler,
        \Magento\Session\ValidatorInterface $validator,
        \Magento\Session\StorageInterface $storage,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\QuoteFactory $quoteFactory,
        \Magento\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        $sessionName = null
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_customerSession = $customerSession;
        $this->_quoteFactory = $quoteFactory;
        $this->_remoteAddress = $remoteAddress;
        parent::__construct($request, $sidResolver, $sessionConfig, $saveHandler, $validator, $storage);
        $this->start($sessionName);
    }

    /**
     * Set customer instance
     *
     * @param \Magento\Customer\Model\Customer|null $customer
     * @return \Magento\Checkout\Model\Session
     */
    public function setCustomer($customer)
    {
        $this->_customer = $customer;
        return $this;
    }

    /**
     * Check whether current session has quote
     *
     * @return bool
     */
    public function hasQuote()
    {
        return isset($this->_quote);
    }

    /**
     * Set quote to be loaded even if inactive
     *
     * @param bool $load
     * @return \Magento\Checkout\Model\Session
     */
    public function setLoadInactive($load = true)
    {
        $this->_loadInactive = $load;
        return $this;
    }

    /**
     * Get checkout quote instance by current session
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        $this->_eventManager->dispatch('custom_quote_process', array('checkout_session' => $this));

        if ($this->_quote === null) {
            /** @var $quote \Magento\Sales\Model\Quote */
            $quote = $this->_quoteFactory->create()->setStoreId($this->_storeManager->getStore()->getId());
            if ($this->getQuoteId()) {
                if ($this->_loadInactive) {
                    $quote->load($this->getQuoteId());
                } else {
                    $quote->loadActive($this->getQuoteId());
                }
                if ($quote->getId()) {
                    /**
                     * If current currency code of quote is not equal current currency code of store,
                     * need recalculate totals of quote. It is possible if customer use currency switcher or
                     * store switcher.
                     */
                    if ($quote->getQuoteCurrencyCode() != $this->_storeManager->getStore()->getCurrentCurrencyCode()) {
                        $quote->setStore($this->_storeManager->getStore());
                        $quote->collectTotals()->save();
                        /*
                         * We mast to create new quote object, because collectTotals()
                         * can to create links with other objects.
                         */
                        $quote = $this->_quoteFactory->create()->setStoreId($this->_storeManager->getStore()->getId());
                        $quote->load($this->getQuoteId());
                    }
                } else {
                    $this->setQuoteId(null);
                }
            }

            if (!$this->getQuoteId()) {
                if ($this->_customerSession->isLoggedIn() || $this->_customer) {
                    $customer = ($this->_customer) ? $this->_customer : $this->_customerSession->getCustomer();
                    $quote->loadByCustomer($customer);
                    $this->setQuoteId($quote->getId());
                } else {
                    $quote->setIsCheckoutCart(true);
                    $this->_eventManager->dispatch('checkout_quote_init', array('quote'=>$quote));
                }
            }

            if ($this->getQuoteId()) {
                if ($this->_customerSession->isLoggedIn() || $this->_customer) {
                    $customer = ($this->_customer) ? $this->_customer : $this->_customerSession->getCustomer();
                    $quote->setCustomer($customer);
                }
            }

            $quote->setStore($this->_storeManager->getStore());
            $this->_quote = $quote;
        }

        if ($remoteAddr = $this->_remoteAddress->getRemoteAddress()) {
            $this->_quote->setRemoteIp($remoteAddr);
            $xForwardIp = $this->request->getServer('HTTP_X_FORWARDED_FOR');
            $this->_quote->setXForwardedFor($xForwardIp);
        }
        return $this->_quote;
    }

    protected function _getQuoteIdKey()
    {
        return 'quote_id_' . $this->_storeManager->getStore()->getWebsiteId();
    }

    public function setQuoteId($quoteId)
    {
        $this->setData($this->_getQuoteIdKey(), $quoteId);
    }

    public function getQuoteId()
    {
        return $this->getData($this->_getQuoteIdKey());
    }

    /**
     * Load data for customer quote and merge with current quote
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function loadCustomerQuote()
    {
        if (!$this->_customerSession->getCustomerId()) {
            return $this;
        }

        $this->_eventManager->dispatch('load_customer_quote_before', array('checkout_session' => $this));

        $customerQuote = $this->_quoteFactory->create()
            ->setStoreId($this->_storeManager->getStore()->getId())
            ->loadByCustomer($this->_customerSession->getCustomerId());

        if ($customerQuote->getId() && $this->getQuoteId() != $customerQuote->getId()) {
            if ($this->getQuoteId()) {
                $customerQuote->merge($this->getQuote())
                    ->collectTotals()
                    ->save();
            }

            $this->setQuoteId($customerQuote->getId());

            if ($this->_quote) {
                $this->_quote->delete();
            }
            $this->_quote = $customerQuote;
        } else {
            $this->getQuote()->getBillingAddress();
            $this->getQuote()->getShippingAddress();
            $this->getQuote()->setCustomer($this->_customerSession->getCustomer())
                ->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save();
        }
        return $this;
    }

    public function setStepData($step, $data, $value=null)
    {
        $steps = $this->getSteps();
        if (is_null($value)) {
            if (is_array($data)) {
                $steps[$step] = $data;
            }
        } else {
            if (!isset($steps[$step])) {
                $steps[$step] = array();
            }
            if (is_string($data)) {
                $steps[$step][$data] = $value;
            }
        }
        $this->setSteps($steps);

        return $this;
    }

    public function getStepData($step=null, $data=null)
    {
        $steps = $this->getSteps();
        if (is_null($step)) {
            return $steps;
        }
        if (!isset($steps[$step])) {
            return false;
        }
        if (is_null($data)) {
            return $steps[$step];
        }
        if (!is_string($data) || !isset($steps[$step][$data])) {
            return false;
        }
        return $steps[$step][$data];
    }

    /**
     * Retrieves list of all saved additional messages for different instances (e.g. quote items) in checkout session
     * Returned: array(itemKey => messageCollection, ...)
     * where itemKey is a unique hash (e.g 'quote_item17') to distinguish item messages among message collections
     *
     * @param bool $clear
     *
     * @return array
     */
    public function getAdditionalMessages($clear = false)
    {
        $additionalMessages = $this->getData('additional_messages');
        if (!$additionalMessages) {
            return array();
        }
        if ($clear) {
            $this->setData('additional_messages', null);
        }
        return $additionalMessages;
    }

    /**
     * Retrieves list of item additional messages
     * itemKey is a unique hash (e.g 'quote_item17') to distinguish item messages among message collections
     *
     * @param string $itemKey
     * @param bool $clear
     *
     * @return null|\Magento\Message\Collection
     */
    public function getItemAdditionalMessages($itemKey, $clear = false)
    {
        $allMessages = $this->getAdditionalMessages();
        if (!isset($allMessages[$itemKey])) {
            return null;
        }

        $messages = $allMessages[$itemKey];
        if ($clear) {
            unset($allMessages[$itemKey]);
            $this->setAdditionalMessages($allMessages);
        }
        return $messages;
    }

    /**
     * Adds new message in this session to a list of additional messages for some item
     * itemKey is a unique hash (e.g 'quote_item17') to distinguish item messages among message collections
     *
     * @param string $itemKey
     * @param \Magento\Message\AbstractMessage $message
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function addItemAdditionalMessage($itemKey, $message)
    {
        $allMessages = $this->getAdditionalMessages();
        if (!isset($allMessages[$itemKey])) {
            $allMessages[$itemKey] = $this->messagesFactory->create();
        }
        $allMessages[$itemKey]->add($message);
        $this->setAdditionalMessages($allMessages);

        return $this;
    }

    /**
     * Retrieves list of quote item messages
     * @param int $itemId
     * @param bool $clear
     *
     * @return null|\Magento\Message\Collection
     */
    public function getQuoteItemMessages($itemId, $clear = false)
    {
        return $this->getItemAdditionalMessages('quote_item' . $itemId, $clear);
    }

    /**
     * Adds new message to a list of quote item messages, saved in this session
     *
     * @param int $itemId
     * @param \Magento\Message\AbstractMessage $message
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function addQuoteItemMessage($itemId, $message)
    {
        return $this->addItemAdditionalMessage('quote_item' . $itemId, $message);
    }

    /**
     * Destroy/end a session
     * Unset all data associated with object
     *
     * @return $this
     */
    public function clearQuote()
    {
        $this->_eventManager->dispatch('checkout_quote_destroy', array('quote' => $this->getQuote()));
        $this->_quote = null;
        $this->setQuoteId(null);
        $this->setLastSuccessQuoteId(null);
        return $this;
    }

    /**
     * Unset all session data and quote
     *
     * @return $this
     */
    public function clearStorage()
    {
        parent::clearStorage();
        $this->_quote = null;
        return $this;
    }

    /**
     * Clear misc checkout parameters
     */
    public function clearHelperData()
    {
        $this->setLastBillingAgreementId(null)
            ->setRedirectUrl(null)
            ->setLastOrderId(null)
            ->setLastRealOrderId(null)
            ->setLastRecurringProfileIds(null)
            ->setAdditionalMessages(null)
        ;
    }

    public function resetCheckout()
    {
        $this->setCheckoutState(self::CHECKOUT_STATE_BEGIN);
        return $this;
    }

    public function replaceQuote($quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }

    /**
     * Get order instance based on last order ID
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getLastRealOrder()
    {
        $orderId = $this->getLastRealOrderId();
        if ($this->_order !== null && $orderId == $this->_order->getIncrementId()) {
            return $this->_order;
        }
        $this->_order = $this->_orderFactory->create();
        if ($orderId) {
            $this->_order->loadByIncrementId($orderId);
        }
        return $this->_order;
    }
}
