<?php
/**
 * Magento Saas Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @category    Saas
 * @package     Saas_Paypal
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * EnterBoarding operation model
 *
 * @deprecated Use NVP implementation to work with permissions
 *
 * @category   Saas
 * @package    Saas_Paypal
 * @author     Magento Saas Team <core@magentocommerce.com>
 */
class Saas_Paypal_Model_Api_Soap_Operation_EnterBoarding extends Saas_Paypal_Model_Api_Soap_Operation_Abstract
{
    /**
     * Operation name for SOAP request.
     *
     * @var string
     */
    protected $_methodName = 'EnterBoarding';

    /**
     * Request array map for the operation between SOAP request and data in Api model.
     *
     * @var array
     */
    protected $_requestMap = array(
        'EnterBoardingRequest' => array(
            'EnterBoardingRequestDetails' => array(
                'ProgramCode' => 'program_code',
                'PartnerCustom' => 'partner_custom',
                'ProductList' => 'product_list',
                'MarketingCategory' => 'marketing_category'
            ),
            'Version' => 'version'
        )
    );

    /**
     * Response array map for the operation between SOAP response and data in Api model.
     *
     * @var array
     */
    protected $_responseMap = array(
        'Token' => 'boarding_token'
    );
}
