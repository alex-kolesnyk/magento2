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
 * @category    Mage
 * @package     Mage_Moneybookers
 * @copyright   Copyright (c) 2009 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Moneybookers_MoneybookersController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Retrieve Moneybookers helper
     *
     * @return Mage_Moneybookers_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('moneybookers');
    }

    /**
     * Send activation Email to Moneybookers
     */
    public function activateemailAction()
    {
        $this->_getHelper()->activateEmail();
    }

    /**
     * Check if email is registered at Moneybookers
     */
    public function checkemailAction()
    {
        try {
            $params = $this->getRequest()->getParams();
            if (empty($params['email'])) {
                Mage::throwException($this->_getHelper()->__('Error: No parameters specified'));
            }
            $response =  $this->_getHelper()->checkEmailRequest($params);
            if (empty($response)) {
                Mage::throwException($this->_getHelper()->__('Error: Connection to moneybookers.com failed'));
            }
        }
        catch (Exception $e) {
            $response = $e->getMessage();
        }
        $this->getResponse()->setBody($response);
    }

    /**
     * Check if entered secret is valid
     */
    public function checksecretAction()
    {
        try {
            $params = $this->getRequest()->getParams();
            if (empty($params['email']) || empty($params['secret'])) {
                throw new Exception($this->_getHelper()->__('Error: No parameters specified'));
            }
            $response =  $this->_getHelper()->checkSecretRequest($params);
            if (empty($response)) {
                Mage::throwException($this->_getHelper()->__('Error: Connection to moneybookers.com failed'));
            }
        }
        catch (Exception $e) {
            $response = $e->getMessage();
        }
        $this->getResponse()->setBody($response);
    }
}
