<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Product
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Enterprise_Mage_Product_Helper extends Core_Mage_Product_Helper
{
    #**************************************************************************************
    #*                                                    Frontend Helper Methods         *
    #**************************************************************************************
    /**
     * Choose custom options and additional products
     *
     * @param array $dataForBuy
     */
    public function frontAddProductToCart($dataForBuy = null)
    {
        $customize = $this->controlIsPresent('button', 'customize_and_add_to_cart');
        $customizeFieldset = $this->_getControlXpath('fieldset', 'customize_product_info');
        if ($customize) {
            $productInfoFieldset = $this->_getControlXpath('fieldset', 'product_info');
            $this->clickButton('customize_and_add_to_cart', false);
            $this->waitForElementVisible($customizeFieldset);
            $this->waitForElement($productInfoFieldset . "/parent::*[@style='display: none;']");
        }
        parent::frontAddProductToCart($dataForBuy);
    }

    /**
     * Verify Gift Card info on frontend
     *
     * @param array $productData
     */
    public function frontVerifyGiftCardInfo(array $productData)
    {
        $this->markTestIncomplete('@TODO - implement frontVerifyGiftCardInfo');
    }

    #**************************************************************************************
    #*                                                    Backend Helper Methods          *
    #**************************************************************************************

    /**
     * Fill in data on General Tab
     *
     * @param array $generalTab
     */
    public function fillGeneralTab(array $generalTab)
    {
        $this->openProductTab('general');
        parent::fillGeneralTab($generalTab);
        if (isset($generalTab['general_gift_card_data'])) {
            foreach ($generalTab['general_gift_card_data']['general_amounts'] as $value) {
                $this->addGiftCardAmount($value);
                unset($generalTab['general_gift_card_data']['general_amounts']);
            }
            $this->fillFieldset($generalTab['general_gift_card_data'], 'general_gift_card_data');
            unset($generalTab['general_gift_card_data']);
        }
    }

//    /**
//     * Verify data on General Tab
//     *
//     * @param array $generalTab
//     */
//    public function verifyGeneralTab($generalTab)
//    {
//        $this->openTab('general');
//        if (isset($generalTab['general_gift_card_data'])) {
//            $this->verifyGiftCardAmounts($generalTab['general_gift_card_data']['general_amounts']);
//            $this->verifyForm($generalTab['general_gift_card_data'], 'general');
//            unset($generalTab['general_gift_card_data']);
//        }
//        parent::verifyGeneralTab($generalTab);
//    }

    /**
     * Add Gift Card Amount
     *
     * @param array $giftCardData
     */
    public function addGiftCardAmount(array $giftCardData)
    {
        $rowNumber = $this->getControlCount('pageelement', 'general_gift_card_amount_line');
        $this->addParameter('giftCardId', $rowNumber);
        $this->clickButton('add_gift_card_amount', false);
        $this->waitForAjax();
        if ($this->controlIsVisible('dropdown', 'general_gift_card_website')) {
            $this->fillDropdown('general_gift_card_website', $giftCardData['general_gift_card_website']);
        }
        $this->fillField('general_gift_card_amount', $giftCardData['general_gift_card_amount']);
    }

    /**
     * Verify GiftCardAmounts
     *
     * @param array $giftCardData
     *
     * @return boolean
     */
    public function verifyGiftCardAmounts(array $giftCardData)
    {
        $rowQty = count($this->getControlElements('pageelement', 'general_gift_card_amount_line', null, false));
        $needCount = count($giftCardData);
        if ($needCount != $rowQty) {
            $this->addVerificationMessage(
                'Product must contain ' . $needCount . ' gift card amount(s), but contains ' . $rowQty);
            return false;
        }
        $index = $rowQty - 1;
        foreach ($giftCardData as $value) {
            $this->addParameter('giftCardId', $index);
            $this->verifyForm($value, 'gift_card_amount');
            $this->verifyForm($value, 'general_amounts');
            $this->verifyForm($value, 'general_gift_card_information');
            --$index;
        }
        $this->assertEmptyVerificationErrors();
        return true;
    }
}
