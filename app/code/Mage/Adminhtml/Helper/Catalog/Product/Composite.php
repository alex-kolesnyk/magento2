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
 * Adminhtml catalog product composite helper
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Helper_Catalog_Product_Composite extends Mage_Core_Helper_Abstract
{
     /**
     * Init layout of product configuration update result
     *
     * @param Mage_Adminhtml_Controller_Action $controller
     * @return Mage_Adminhtml_Helper_Catalog_Product_Composite
     */
    protected function _initUpdateResultLayout($controller)
    {
        $controller->getLayout()->getUpdate()
            ->addHandle('ADMINHTML_CATALOG_PRODUCT_COMPOSITE_UPDATE_RESULT');
        $controller->loadLayoutUpdates()->generateLayoutXml()->generateLayoutBlocks();
        return $this;
    }

    /**
     * Prepares and render result of composite product configuration update for a case
     * when single configuration submitted
     *
     * @param Mage_Adminhtml_Controller_Action $controller
     * @param Magento_Object $updateResult
     * @return Mage_Adminhtml_Helper_Catalog_Product_Composite
     */
    public function renderUpdateResult($controller, Magento_Object $updateResult)
    {
        Mage::register('composite_update_result', $updateResult);

        $this->_initUpdateResultLayout($controller);
        $controller->renderLayout();
    }

     /**
     * Init composite product configuration layout
     *
     * $isOk - true or false, whether action was completed nicely or with some error
     * If $isOk is FALSE (some error during configuration), so $productType must be null
     *
     * @param Mage_Adminhtml_Controller_Action $controller
     * @param bool $isOk
     * @param string $productType
     * @return Mage_Adminhtml_Helper_Catalog_Product_Composite
     */
    protected function _initConfigureResultLayout($controller, $isOk, $productType)
    {
        $update = $controller->getLayout()->getUpdate();
        if ($isOk) {
            $update->addHandle('ADMINHTML_CATALOG_PRODUCT_COMPOSITE_CONFIGURE')
                ->addHandle('catalog_product_view_type_' . $productType);
        } else {
            $update->addHandle('ADMINHTML_CATALOG_PRODUCT_COMPOSITE_CONFIGURE_ERROR');
        }
        $controller->loadLayoutUpdates()->generateLayoutXml()->generateLayoutBlocks();
        return $this;
    }

    /**
     * Prepares and render result of composite product configuration request
     *
     * $configureResult holds either:
     *  - 'ok' = true, and 'product_id', 'buy_request', 'current_store_id', 'current_customer' or 'current_customer_id'
     *  - 'error' = true, and 'message' to show
     *
     * @param Mage_Adminhtml_Controller_Action $controller
     * @param Magento_Object $configureResult
     * @return Mage_Adminhtml_Helper_Catalog_Product_Composite
     */
    public function renderConfigureResult($controller, Magento_Object $configureResult)
    {
        try {
            if (!$configureResult->getOk()) {
                Mage::throwException($configureResult->getMessage());
            };

            $currentStoreId = (int) $configureResult->getCurrentStoreId();
            if (!$currentStoreId) {
                $currentStoreId = Mage::app()->getStore()->getId();
            }

            $product = Mage::getModel('Mage_Catalog_Model_Product')
                ->setStoreId($currentStoreId)
                ->load($configureResult->getProductId());
            if (!$product->getId()) {
                Mage::throwException($this->__('The product is not loaded.'));
            }
            Mage::register('current_product', $product);
            Mage::register('product', $product);

            // Register customer we're working with
            $currentCustomer = $configureResult->getCurrentCustomer();
            if (!$currentCustomer) {
                $currentCustomerId = (int) $configureResult->getCurrentCustomerId();
                if ($currentCustomerId) {
                    $currentCustomer = Mage::getModel('Mage_Customer_Model_Customer')
                        ->load($currentCustomerId);
                }
            }
            if ($currentCustomer) {
                Mage::register('current_customer', $currentCustomer);
            }

            // Prepare buy request values
            $buyRequest = $configureResult->getBuyRequest();
            if ($buyRequest) {
                Mage::helper('Mage_Catalog_Helper_Product')->prepareProductOptions($product, $buyRequest);
            }

            $isOk = true;
            $productType = $product->getTypeId();
        } catch (Exception $e) {
            $isOk = false;
            $productType = null;
            Mage::register('composite_configure_result_error_message', $e->getMessage());
        }

        $this->_initConfigureResultLayout($controller, $isOk, $productType);
        $controller->renderLayout();
    }
}
