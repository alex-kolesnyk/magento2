<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Payment
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Payment configuration model
 *
 * Used for retrieving configuration data by payment models
 */
namespace Magento\Payment\Model;

class Config
{
    protected static $_methods;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Core\Model\Config
     */
    protected $_coreConfig;

    /**
     * Locale model
     *
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * Payment method factory
     *
     * @var \Magento\Payment\Model\Method\Factory
     */
    protected $_methodFactory;

    /**
     * Construct
     *
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\Config $coreConfig
     * @param \Magento\Payment\Model\Method\Factory $paymentMethodFactory
     * @param \Magento\Core\Model\LocaleInterface $locale
     */
    public function __construct(
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\Config $coreConfig,
        \Magento\Payment\Model\Method\Factory $paymentMethodFactory,
        \Magento\Core\Model\LocaleInterface $locale
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_coreConfig = $coreConfig;
        $this->_methodFactory = $paymentMethodFactory;
        $this->_locale = $locale;
    }

    /**
     * Retrieve active system payments
     *
     * @param   mixed $store
     * @return  array
     */
    public function getActiveMethods($store=null)
    {
        $methods = array();
        $config = $this->_coreStoreConfig->getConfig('payment', $store);
        foreach ($config as $code => $methodConfig) {
            if ($this->_coreStoreConfig->getConfigFlag('payment/'.$code.'/active', $store)) {
                if (array_key_exists('model', $methodConfig)) {
                    $methodModel = $this->_methodFactory->create($methodConfig['model']);
                    if ($methodModel && $methodModel->getConfigData('active', $store)) {
                        $methods[$code] = $this->_getMethod($code, $methodConfig);
                    }
                }
            }
        }
        return $methods;
    }

    /**
     * Retrieve all system payments
     *
     * @param mixed $store
     * @return array
     */
    public function getAllMethods($store=null)
    {
        $methods = array();
        $config = $this->_coreStoreConfig->getConfig('payment', $store);
        foreach ($config as $code => $methodConfig) {
            $data = $this->_getMethod($code, $methodConfig);
            if (false !== $data) {
                $methods[$code] = $data;
            }
        }
        return $methods;
    }

    protected function _getMethod($code, $config, $store=null)
    {
        if (isset(self::$_methods[$code])) {
            return self::$_methods[$code];
        }
        if (empty($config['model'])) {
            return false;
        }
        $modelName = $config['model'];

        if (!class_exists($modelName)) {
            return false;
        }

        $method = $this->_methodFactory->create($modelName);
        $method->setId($code)->setStore($store);
        self::$_methods[$code] = $method;
        return self::$_methods[$code];
    }

    /**
     * Retrieve array of credit card types
     *
     * @return array
     */
    public function getCcTypes()
    {
        $_types = $this->_coreConfig->getNode('global/payment/cc/types')->asArray();

        uasort($_types, array('Magento\Payment\Model\Config', 'compareCcTypes'));

        $types = array();
        foreach ($_types as $data) {
            if (isset($data['code']) && isset($data['name'])) {
                $types[$data['code']] = $data['name'];
            }
        }
        return $types;
    }

    /**
     * Retrieve list of months translation
     *
     * @return array
     */
    public function getMonths()
    {
        $data = $this->_locale->getTranslationList('month');
        foreach ($data as $key => $value) {
            $monthNum = ($key < 10) ? '0'.$key : $key;
            $data[$key] = $monthNum . ' - ' . $value;
        }
        return $data;
    }

    /**
     * Retrieve array of available years
     *
     * @return array
     */
    public function getYears()
    {
        $years = array();
        $first = date("Y");

        for ($index=0; $index <= 10; $index++) {
            $year = $first + $index;
            $years[$year] = $year;
        }
        return $years;
    }

    /**
     * Statis Method for compare sort order of CC Types
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    static function compareCcTypes($a, $b)
    {
        if (!isset($a['order'])) {
            $a['order'] = 0;
        }

        if (!isset($b['order'])) {
            $b['order'] = 0;
        }

        if ($a['order'] == $b['order']) {
            return 0;
        } else if ($a['order'] > $b['order']) {
            return 1;
        } else {
            return -1;
        }
    }
}
