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
 * @category   Mage
 * @package    Mage_Chronopay
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Failure Response from Chronopay
 *
 * @category   Mage
 * @package    Mage_Chronopay
 * @name       Mage_Chronopay_Block_Standard_Failure
 * @author     Dmitriy Volik <dmitriy.volik@varien.com>
 */

class Mage_Chronopay_Block_Standard_Failure extends Mage_Core_Block_Template
{
    /**
     *  Return StatusDetail field value from Response
     *
     *  @return	  string
     */
    public function getErrorMessage ()
    {
        return Mage::getSingleton('checkout/session')->getErrorMessage();
    }

    /**
     * Get continue shopping url
     */
    public function getContinueShoppingUrl()
    {
        return Mage::getUrl('checkout/cart');
    }
}