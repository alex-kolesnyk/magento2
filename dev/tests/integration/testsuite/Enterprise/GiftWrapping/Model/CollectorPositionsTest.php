<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test positions of the GiftWrapping total collectors as compared to other collectors
 */
class Enterprise_GiftWrapping_Model_CollectorPositionsTest extends Magento_Sales_Model_CollectorPositionsTestAbstract
{
    /**
     * @return array
     */
    public function collectorPositionDataProvider()
    {
        return array(
            'quote collectors' => array(
                'giftwrapping',
                'quote',
                array(),
                array('subtotal'),
            ),
            /** MAGETWO-10266
            'invoice collectors' => array(
                'giftwrapping',
                'quote',
                array('giftcardaccount'),
                array('cost_total'),
            ),
            */
            'creditmemo collectors' => array(
                'giftwrapping',
                'creditmemo',
                array('giftcardaccount'),
                array('cost_total'),
            ),
            'tax quote collectors' => array(
                'tax_giftwrapping',
                'quote',
                array('grand_total'),
                array('tax'),
            ),
            'tax invoice collectors' => array(
                'tax_giftwrapping',
                'quote',
                array('grand_total'),
                array('tax'),
            ),
            'tax creditmemo collectors' => array(
                'tax_giftwrapping',
                'creditmemo',
                array('grand_total'),
                array('tax'),
            ),
        );
    }
}
