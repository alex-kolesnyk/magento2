<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Calendar block for page header
 * Prepares localization data for calendar
 *
 * @category   Magento
 * @package    Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Core_Block_Html_Calendar extends Magento_Core_Block_Template
{
    /**
     * Date model
     *
     * @var Magento_Core_Model_Date
     */
    protected $_date;

    /**
     * Core locale
     *
     * @var Magento_Core_Model_LocaleInterface
     */
    protected $_locale;

    /**
     * @param Magento_Core_Model_LocaleInterface $locale
     * @param Magento_Core_Model_Date $date
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Core_Block_Template_Context $context
     * @param array $data
     */
    public function __construct(
        Magento_Core_Model_LocaleInterface $locale,
        Magento_Core_Model_Date $date,
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Block_Template_Context $context,
        array $data = array()
    ) {
        $this->_locale = $locale;
        $this->_date = $date;
        parent::__construct($coreData, $context, $data);
    }

    protected function _toHtml()
    {
        $localeCode = $this->_locale->getLocaleCode();

        // get days names
        $days = Zend_Locale_Data::getList($localeCode, 'days');
        $helper = $this->_coreData;
        $this->assign('days', array(
            'wide'        => $helper->jsonEncode(array_values($days['format']['wide'])),
            'abbreviated' => $helper->jsonEncode(array_values($days['format']['abbreviated']))
        ));

        // get months names
        $months = Zend_Locale_Data::getList($localeCode, 'months');
        $this->assign('months', array(
            'wide'        => $helper->jsonEncode(array_values($months['format']['wide'])),
            'abbreviated' => $helper->jsonEncode(array_values($months['format']['abbreviated']))
        ));

        // get "today" and "week" words
        $this->assign('today', $helper->jsonEncode(Zend_Locale_Data::getContent($localeCode, 'relative', 0)));
        $this->assign('week', $helper->jsonEncode(Zend_Locale_Data::getContent($localeCode, 'field', 'week')));

        // get "am" & "pm" words
        $this->assign('am', $helper->jsonEncode(Zend_Locale_Data::getContent($localeCode, 'am')));
        $this->assign('pm', $helper->jsonEncode(Zend_Locale_Data::getContent($localeCode, 'pm')));

        // get first day of week and weekend days
        $this->assign('firstDay',    (int)Mage::getStoreConfig('general/locale/firstday'));
        $this->assign('weekendDays', $helper->jsonEncode((string)Mage::getStoreConfig('general/locale/weekend')));

        // define default format and tooltip format
        $this->assign(
            'defaultFormat',
            $helper->jsonEncode($this->_locale->getDateFormat(Magento_Core_Model_LocaleInterface::FORMAT_TYPE_MEDIUM))
        );
        $this->assign(
            'toolTipFormat',
            $helper->jsonEncode($this->_locale->getDateFormat(Magento_Core_Model_LocaleInterface::FORMAT_TYPE_LONG))
        );

        // get days and months for en_US locale - calendar will parse exactly in this locale
        $days = Zend_Locale_Data::getList('en_US', 'days');
        $months = Zend_Locale_Data::getList('en_US', 'months');
        $enUS = new stdClass();
        $enUS->m = new stdClass();
        $enUS->m->wide = array_values($months['format']['wide']);
        $enUS->m->abbr = array_values($months['format']['abbreviated']);
        $this->assign('enUS', $helper->jsonEncode($enUS));

        return parent::_toHtml();
    }

    /**
     * Return offset of current timezone with GMT in seconds
     *
     * @return integer
     */
    public function getTimezoneOffsetSeconds()
    {
        return $this->_date->getGmtOffset();
    }

    /**
     * Getter for store timestamp based on store timezone settings
     *
     * @param mixed $store
     * @return int
     */
    public function getStoreTimestamp($store = null)
    {
        return $this->_locale->storeTimeStamp($store);
    }
}
