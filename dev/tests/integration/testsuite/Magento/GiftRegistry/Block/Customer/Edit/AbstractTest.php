<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_GiftRegistry
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for \Magento\GiftRegistry\Block\Customer\Edit\AbstractEdit
 */
namespace Magento\GiftRegistry\Block\Customer\Edit;

class AbstractTest
    extends \PHPUnit_Framework_TestCase
{
    /**
     * Stub class name
     */
    const STUB_CLASS = 'Magento_GiftRegistry_Block_Customer_Edit_AbstractEdit_Stub';

    public function testGetCalendarDateHtml()
    {
        $this->getMockForAbstractClass(
            'Magento\GiftRegistry\Block\Customer\Edit\AbstractEdit', array(), self::STUB_CLASS, false
        );
        $block = \Mage::app()->getLayout()->createBlock(self::STUB_CLASS);

        $value = null;
        $formatType = \Magento\Core\Model\LocaleInterface::FORMAT_TYPE_MEDIUM;

        $html = $block->getCalendarDateHtml('date_name', 'date_id', $value, $formatType);

        $dateFormat = \Mage::app()->getLocale()->getDateFormat($formatType);
        $value = $block->formatDate($value, $formatType);

        $this->assertContains('dateFormat: "' . $dateFormat . '",', $html);
        $this->assertContains('value="' . $value . '"', $html);
    }
}
