<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_DirectPayment
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Direct Payment Data Helper
 *
 * @category   Mage
 * @package    Mage_DirectPayment
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_DirectPayment_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieve save order url params
     *
     * @param string $controller
     * @return array
     */
    public function getSaveOrderUrlParams($controller)
    {
        $route = array();
        switch ($controller) {
            case 'onepage':
                $route['action'] = 'saveOrder';
                $route['controller'] = 'onepage';
                $route['module'] = 'checkout';
                break;
            case 'sales_order_create':
            case 'sales_order_edit':
                $route['action'] = 'save';
                $route['controller'] = 'sales_order_create';
                $route['module'] = 'admin';
                break;
        }        
        
        return $route;
    }
    
    /**
     * Retrieve redirect ifrmae url
     *     
     * @param array params
     * @return string
     */
    public function getRedirectIframeUrl($params)
    {
        switch ($params['controller_action_name']) {
            case 'onepage':
                $route = 'directpayment/paygate/redirect';
                break;
            case 'sales_order_create':
            case 'sales_order_edit':
                $route = 'adminhtml/directpayment_paygate/redirect';
                break;
            default:
                $route = 'directpayment/paygate/redirect';
                break;
        }
        
        return $this->_getUrl($route, $params);
    }
    
    /**
     * Retrieve place order url on front
     *
     * @return  string
     */
    public function getPlaceOrderFrontUrl()
    {
        return $this->_getUrl('directpayment/paygate/place');
    }
    
    /**
     * Retrieve place order url in admin
     *
     * @return  string
     */
    public function getPlaceOrderAdminUrl()
    {
        return $this->_getUrl('*/directpayment_paygate/place');
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
        switch ($params['controller_action_name']) {
            case 'onepage':
                $route = 'checkout/onepage/success';
                break;
            case 'sales_order_create':
            case 'sales_order_edit':
                $route = 'adminhtml/sales_order/view';
                $order = Mage::getModel('sales/order')->loadByIncrementId($params['x_invoice_num']);
                $param['order_id'] = $order->getId();
                break;
            default :
                $route = 'checkout/onepage/success';
                break;
        }
        
        return $this->_getUrl($route, $param);
    }
    
    /**
     * Get controller name
     *
     * @return string
     */
    public function getControllerName()
    {
        return Mage::app()->getFrontController()->getRequest()->getControllerName();
    }
}
