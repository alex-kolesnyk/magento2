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
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Sales_Model_Order_Status extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('sales/order_status');
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getFrontendLabel()
    {
        $label = '';
        if ($storeId = Mage::getSingleton('core/store')->getId()) {
            $label = Mage::getSingleton('core/store')->getConfig('sales/order_statuses/status_' . $this->getId());
        }
        if (! $label) {
            $label = $this->getData('frontend_label');
        }
        return $label;
    }

}
