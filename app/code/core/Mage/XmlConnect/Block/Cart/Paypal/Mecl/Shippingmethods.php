<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * PayPal MECL Shipping method list xml renderer
 *
 * @category    Mage
 * @package     Mage_Xmlconnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Cart_Paypal_Mecl_Shippingmethods
    extends Mage_Paypal_Block_Express_Review
{
    /**
     * Render PayPal MECL shipping method list xml
     *
     * @return string xml
     */
    protected function _toHtml()
    {
        /** @var $listXmlObj Mage_XmlConnect_Model_Simplexml_Element */
        $methodListXmlObj = Mage::getModel(
            'Mage_XmlConnect_Model_Simplexml_Element',
            array('data' => '<shipping_method_list></shipping_method_list>')
        );

        $methodListXmlObj->addAttribute('label', $this->__('Shipping Method'));

        if ($this->getCanEditShippingMethod() || !$this->getCurrentShippingRate()) {
            $groups = $this->getShippingRateGroups();
            if ($groups) {
                $currentRate = $this->getCurrentShippingRate();
                foreach ($groups as $code => $rates) {
                    $rateXmlObj = $this->_addRatesToXmlObj($methodListXmlObj, $code);
                    foreach ($rates as $rate) {
                        $rateAttributes = array(
                            'label' => strip_tags($this->renderShippingRateOption($rate)),
                            'code' => $this->renderShippingRateValue($rate)
                        );
                        if ($currentRate === $rate) {
                            $rateAttributes += array('selected' => 1);
                        }
                        $rateXmlObj->addCustomChild('rate', null, $rateAttributes);
                    }
                }
            } else {
                $message = $this->_quote->isVirtual() ? $this->__('No shipping method required.')
                    : $this->__('Sorry, no quotes are available for this order at this time.');
                $methodListXmlObj->addCustomChild('method', null, array('label' => $message));
            }
        } else {
            $rateXmlObj = $this->_addRatesToXmlObj($methodListXmlObj);
            $rateXmlObj->addCustomChild('rate', null, array(
                'label' => $this->renderShippingRateOption($this->getCurrentShippingRate()),
                'selected' => 1
            ));
        }

        return $methodListXmlObj->asNiceXml();
    }

    /**
     * Add cart details to XML object
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $methodListXmlObj
     * @param string $code
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    protected function _addRatesToXmlObj(Mage_XmlConnect_Model_Simplexml_Element $methodListXmlObj, $code = '')
    {
        $attributes = $code ? array('label' => $this->getCarrierName($code)) : array();
        return $methodListXmlObj->addCustomChild('method', null, $attributes)->addCustomChild('rates');
    }
}
