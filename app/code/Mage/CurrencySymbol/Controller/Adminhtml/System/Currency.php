<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Currency controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_CurrencySymbol_Controller_Adminhtml_System_Currency extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init currency by currency code from request
     *
     * @return Mage_CurrencySymbol_Controller_Adminhtml_System_Currency
     */
    protected function _initCurrency()
    {
        $code = $this->getRequest()->getParam('currency');
        $currency = Mage::getModel('Mage_Directory_Model_Currency')
            ->load($code);

        Mage::register('currency', $currency);
        return $this;
    }

    /**
     * Currency management main page
     */
    public function indexAction()
    {
        $this->_title(__('Currency Rates'));

        $this->loadLayout();
        $this->_setActiveMenu('Mage_CurrencySymbol::system_currency_rates');
        $this->_addContent($this->getLayout()->createBlock('Mage_CurrencySymbol_Block_Adminhtml_System_Currency'));
        $this->renderLayout();
    }

    public function fetchRatesAction()
    {
        try {
            $service = $this->getRequest()->getParam('rate_services');
            $this->_getSession()->setCurrencyRateService($service);
            if( !$service ) {
                throw new Exception(__('Please specify a correct Import Service.'));
            }
            try {
                $importModel = Mage::getModel(
                    Mage::getConfig()->getNode('global/currency/import/services/' . $service . '/model')->asArray()
                );
            } catch (Exception $e) {
                Mage::throwException(__('We can\'t initialize the import model.'));
            }
            $rates = $importModel->fetchRates();
            $errors = $importModel->getMessages();
            if( sizeof($errors) > 0 ) {
                foreach ($errors as $error) {
                    Mage::getSingleton('Mage_Adminhtml_Model_Session')->addWarning($error);
                }
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addWarning(__('All possible rates were fetched, please click on "Save" to apply'));
            } else {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(__('All rates were fetched, please click on "Save" to apply'));
            }

            Mage::getSingleton('Mage_Adminhtml_Model_Session')->setRates($rates);
        }
        catch (Exception $e){
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    public function saveRatesAction()
    {
        $data = $this->getRequest()->getParam('rate');
        if( is_array($data) ) {
            try {
                foreach ($data as $currencyCode => $rate) {
                    foreach( $rate as $currencyTo => $value ) {
                        $value = abs(Mage::getSingleton('Mage_Core_Model_LocaleInterface')->getNumber($value));
                        $data[$currencyCode][$currencyTo] = $value;
                        if( $value == 0 ) {
                            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addWarning(__('Please correct the input data for %1 => %2 rate', $currencyCode, $currencyTo));
                        }
                    }
                }

                Mage::getModel('Mage_Directory_Model_Currency')->saveRates($data);
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(__('All valid rates have been saved.'));
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mage_CurrencySymbol::currency_rates');
    }
}
