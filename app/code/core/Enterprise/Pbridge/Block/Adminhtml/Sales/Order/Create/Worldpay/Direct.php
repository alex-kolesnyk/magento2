<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Worldpay payment block
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Pbridge_Block_Adminhtml_Sales_Order_Create_Worldpay_Direct
    extends Enterprise_Pbridge_Block_Adminhtml_Sales_Order_Create_Abstract
{
    /**
     * Worldpay payment code
     *
     * @var string
     */
    protected $_code = 'worlpay_direct';
}
