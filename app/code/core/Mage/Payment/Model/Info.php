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
 * @package    Mage_Payment
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Payment information model
 *
 * @category   Mage
 * @package    Mage_Payment
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Payment_Model_Info extends Mage_Core_Model_Abstract
{
    /**
     * Retrieve store identifier
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    /**
     * Retrieve credit card number
     *
     * @return mixed
     */
    public function getCcNumber()
    {
        if (!$this->getData('cc_number') && $this->getData('cc_number_enc')) {
            $this->setData('cc_number', $this->_decrypt($this->getData('cc_number_enc')));
        }
        return $this->getData('cc_number');
    }

    /**
     * Retrieve credit card verification number
     *
     * @return mixed
     */
    public function getCcCid()
    {
        if (!$this->getData('cc_cid') && $this->getData('cc_cid_enc')) {
            $this->setData('cc_cid', $this->_decrypt($this->getData('cc_cid_enc')));
        }
        return $this->getData('cc_cid');
    }

    /**
     * Retrieve payment method model object
     *
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function getMethodInstance()
    {
        if ($method = $this->getMethod()) {
            if ($instance = Mage::helper('payment')->getMethodInstance($this->getMethod())) {
                $instance->setInfoInstance($this);
                return $instance;
            }
        }
        Mage::throwException(Mage::helper('payment')->__('Can not retrieve payment method instance'));
    }

    /**
     * Encrypt data
     *
     * @param   string $data
     * @return  string
     */
    protected function _encrypt($data)
    {
        return Mage::helper('core')->encrypt($data);
    }

    /**
     * Decrypt data
     *
     * @param   string $data
     * @return  string
     */
    protected function _decrypt($data)
    {
        return Mage::helper('core')->decrypt($data);
    }
}