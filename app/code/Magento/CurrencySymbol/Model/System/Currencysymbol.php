<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_CurrencySymbol
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Custom currency symbol model
 *
 * @category    Magento
 * @package     Magento_CurrencySymbol
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CurrencySymbol\Model\System;

class Currencysymbol
{
    /**
     * Custom currency symbol properties
     *
     * @var array
     */
    protected $_symbolsData = array();

    /**
     * Store id
     *
     * @var string | null
     */
    protected $_storeId;

    /**
     * Website id
     *
     * @var string | null
     */
    protected $_websiteId;
    /**
     * Cache types which should be invalidated
     *
     * @var array
     */
    protected $_cacheTypes = array(
        \Magento\Core\Model\Cache\Type\Config::TYPE_IDENTIFIER,
        \Magento\Core\Model\Cache\Type\Block::TYPE_IDENTIFIER,
        \Magento\Core\Model\Cache\Type\Layout::TYPE_IDENTIFIER,
    );

    /**
     * Config path to custom currency symbol value
     */
    const XML_PATH_CUSTOM_CURRENCY_SYMBOL = 'currency/options/customsymbol';
    const XML_PATH_ALLOWED_CURRENCIES     = 'currency/options/allow';

    /*
     * Separator used in config in allowed currencies list
     */
    const ALLOWED_CURRENCIES_CONFIG_SEPARATOR = ',';

    /**
     * Config currency section
     */
    const CONFIG_SECTION = 'currency';

    /**
     * Core event manager proxy
     *
     * @var \Magento\Core\Model\Event\Manager
     */
    protected $_eventManager = null;

    /**
     * @param \Magento\Core\Model\Event\Manager $eventManager
     */
    public function __construct(
        \Magento\Core\Model\Event\Manager $eventManager
    ) {
        $this->_eventManager = $eventManager;
    }

    /**
     * Sets store Id
     *
     * @param  $storeId
     * @return \Magento\CurrencySymbol\Model\System\Currencysymbol
     */
    public function setStoreId($storeId=null)
    {
        $this->_storeId = $storeId;
        $this->_symbolsData = array();

        return $this;
    }

    /**
     * Sets website Id
     *
     * @param  $websiteId
     * @return \Magento\CurrencySymbol\Model\System\Currencysymbol
     */
    public function setWebsiteId($websiteId=null)
    {
        $this->_websiteId = $websiteId;
        $this->_symbolsData = array();

        return $this;
    }

    /**
     * Returns currency symbol properties array based on config values
     *
     * @return array
     */
    public function getCurrencySymbolsData()
    {
        if ($this->_symbolsData) {
            return $this->_symbolsData;
        }

        $this->_symbolsData = array();

        $allowedCurrencies = explode(
            self::ALLOWED_CURRENCIES_CONFIG_SEPARATOR,
            \Mage::getStoreConfig(self::XML_PATH_ALLOWED_CURRENCIES, null)
        );

        /* @var $storeModel \Magento\Core\Model\System\Store */
        $storeModel = \Mage::getSingleton('Magento\Core\Model\System\Store');
        foreach ($storeModel->getWebsiteCollection() as $website) {
            $websiteShow = false;
            foreach ($storeModel->getGroupCollection() as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                foreach ($storeModel->getStoreCollection() as $store) {
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $websiteShow = true;
                        $websiteSymbols  = $website->getConfig(self::XML_PATH_ALLOWED_CURRENCIES);
                        $allowedCurrencies = array_merge($allowedCurrencies, explode(
                            self::ALLOWED_CURRENCIES_CONFIG_SEPARATOR,
                            $websiteSymbols
                        ));
                    }
                    $storeSymbols = \Mage::getStoreConfig(self::XML_PATH_ALLOWED_CURRENCIES, $store);
                    $allowedCurrencies = array_merge($allowedCurrencies, explode(
                        self::ALLOWED_CURRENCIES_CONFIG_SEPARATOR,
                        $storeSymbols
                    ));
                }
            }
        }
        ksort($allowedCurrencies);

        $currentSymbols = $this->_unserializeStoreConfig(self::XML_PATH_CUSTOM_CURRENCY_SYMBOL);

        /** @var $locale \Magento\Core\Model\LocaleInterface */
        $locale = \Mage::app()->getLocale();
        foreach ($allowedCurrencies as $code) {
            if (!$symbol = $locale->getTranslation($code, 'currencysymbol')) {
                $symbol = $code;
            }
            $name = $locale->getTranslation($code, 'nametocurrency');
            if (!$name) {
                $name = $code;
            }
            $this->_symbolsData[$code] = array(
                'parentSymbol'  => $symbol,
                'displayName' => $name
            );

            if (isset($currentSymbols[$code]) && !empty($currentSymbols[$code])) {
                $this->_symbolsData[$code]['displaySymbol'] = $currentSymbols[$code];
            } else {
                $this->_symbolsData[$code]['displaySymbol'] = $this->_symbolsData[$code]['parentSymbol'];
            }
            if ($this->_symbolsData[$code]['parentSymbol'] == $this->_symbolsData[$code]['displaySymbol']) {
                $this->_symbolsData[$code]['inherited'] = true;
            } else {
                $this->_symbolsData[$code]['inherited'] = false;
            }
        }

        return $this->_symbolsData;
    }

    /**
     * Saves currency symbol to config
     *
     * @param  $symbols array
     * @return \Magento\CurrencySymbol\Model\System\Currencysymbol
     */
    public function setCurrencySymbolsData($symbols=array())
    {
        foreach ($this->getCurrencySymbolsData() as $code => $values) {
            if (isset($symbols[$code])) {
                if ($symbols[$code] == $values['parentSymbol'] || empty($symbols[$code]))
                unset($symbols[$code]);
            }
        }
        if ($symbols) {
            $value['options']['fields']['customsymbol']['value'] = serialize($symbols);
        } else {
            $value['options']['fields']['customsymbol']['inherit'] = 1;
        }

        \Mage::getModel('Magento\Backend\Model\Config')
            ->setSection(self::CONFIG_SECTION)
            ->setWebsite(null)
            ->setStore(null)
            ->setGroups($value)
            ->save();

        $this->_eventManager->dispatch('admin_system_config_changed_section_currency_before_reinit',
            array('website' => $this->_websiteId, 'store' => $this->_storeId)
        );

        // reinit configuration
        \Mage::getConfig()->reinit();
        \Mage::app()->reinitStores();

        $this->clearCache();

        $this->_eventManager->dispatch('admin_system_config_changed_section_currency',
            array('website' => $this->_websiteId, 'store' => $this->_storeId)
        );

        return $this;
    }

    /**
     * Returns custom currency symbol by currency code
     *
     * @param  $code
     * @return bool|string
     */
    public function getCurrencySymbol($code)
    {
        $customSymbols = $this->_unserializeStoreConfig(self::XML_PATH_CUSTOM_CURRENCY_SYMBOL);
        if (array_key_exists($code, $customSymbols)) {
            return $customSymbols[$code];
        }

        return false;
    }

    /**
     * Clear translate cache
     *
     * @return \Magento\CurrencySymbol\Model\System\Currencysymbol
     */
    public function clearCache()
    {
        /** @var \Magento\Core\Model\Cache\TypeListInterface $cacheTypeList */
        $cacheTypeList = \Mage::getObjectManager()->get('Magento\Core\Model\Cache\TypeListInterface');
        // clear cache for frontend
        foreach ($this->_cacheTypes as $cacheType) {
            $cacheTypeList->invalidate($cacheType);
        }
        return $this;
    }

    /**
     * Unserialize data from Store Config.
     *
     * @param string $configPath
     * @param int $storeId
     * @return array
     */
    protected function _unserializeStoreConfig($configPath, $storeId = null)
    {
        $result = array();
        $configData = (string)\Mage::getStoreConfig($configPath, $storeId);
        if ($configData) {
            $result = unserialize($configData);
        }

        return is_array($result) ? $result : array();
    }
}
