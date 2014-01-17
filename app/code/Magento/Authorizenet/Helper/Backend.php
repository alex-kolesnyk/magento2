<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Authorizenet
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Authorizenet\Helper;

/**
 * Authorizenet Backend Data Helper
 */
class Backend extends Data
{
    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Backend\Model\Url $backendUrl
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Backend\Model\Url $backendUrl
    ) {
        parent::__construct($context, $storeManager, $orderFactory);
        $this->_urlBuilder = $backendUrl;
    }


    /**
     * Return URL for admin area
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _getUrl($route, $params = array())
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }

    /**
     * Retrieve place order url in admin
     *
     * @return  string
     */
    public function getPlaceOrderAdminUrl()
    {
        return $this->_getUrl('adminhtml/authorizenet_directpost_payment/place', array());
    }

    /**
     * Retrieve place order url
     *
     * @param array params
     * @return  string
     */
    public function getSuccessOrderUrl($params)
    {
        $param = array();
        $route = 'sales/order/view';
        $order = $this->_orderFactory->create()->loadByIncrementId($params['x_invoice_num']);
        $param['order_id'] = $order->getId();
        return $this->_getUrl($route, $param);
    }

    /**
     * Retrieve redirect ifrmae url
     *
     * @param array params
     * @return string
     */
    public function getRedirectIframeUrl($params)
    {
        return $this->_getUrl('adminhtml/authorizenet_directpost_payment/redirect', $params);
    }

    /**
     * Get direct post rely url
     *
     * @param null|int|string $storeId
     * @return string
     */
    public function getRelyUrl($storeId = null)
    {
        return $this->_storeManager->getDefaultStoreView()->getBaseUrl(\Magento\Core\Model\Store::URL_TYPE_LINK)
            . 'authorizenet/directpost_payment/backendResponse';
    }
}
