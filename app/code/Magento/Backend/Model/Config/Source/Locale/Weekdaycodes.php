<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Locale weekdays source
 */
namespace Magento\Backend\Model\Config\Source\Locale;

class Weekdaycodes implements \Magento\Core\Model\Option\ArrayInterface
{
    /**
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * @param \Magento\Core\Model\LocaleInterface $locale
     */
    public function __construct(\Magento\Core\Model\LocaleInterface $locale)
    {
        $this->_locale = $locale;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_locale->getOptionWeekdays(true, true);
    }
}
