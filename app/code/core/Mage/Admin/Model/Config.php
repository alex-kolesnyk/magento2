<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Admin
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Configuration for Admin model
 *
 * @category   Mage
 * @package    Mage_Admin
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Admin_Model_Config extends Varien_Simplexml_Config
{
    /**
     * adminhtml.xml merged config
     *
     * @var Varien_Simplexml_Config
     */
    protected $_adminhtmlConfig;

    /**
     * Main Application object
     *
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * Main Application config
     *
     * @var Mage_Core_Model_Config
     */
    protected $_appConfig;

    /**
     * List of helpers by module
     *
     * @var array
     */
    protected $_helpers = array();

    /**
     * Load config from merged adminhtml.xml files
     * @param array $arguments
     */
    public function __construct(array $arguments = array())
    {
        $this->_app = isset($arguments['app']) ? $arguments['app'] : Mage::app();
        $this->_appConfig = isset($arguments['appConfig']) ? $arguments['appConfig'] : Mage::getConfig();
        if (isset($arguments['helpers'])) {
            $this->_helpers = $arguments['helpers'];
        }


        parent::__construct();
        $this->setCacheId('adminhtml_acl_menu_config');

        /* @var $adminhtmlConfig Varien_Simplexml_Config */
        $adminhtmlConfig = $this->_app->loadCache($this->getCacheId());
        if ($adminhtmlConfig) {
            $this->_adminhtmlConfig = new Varien_Simplexml_Config($adminhtmlConfig);
        } else {
            $adminhtmlConfig = new Varien_Simplexml_Config;
            $adminhtmlConfig->loadString('<?xml version="1.0"?><config></config>');
            $this->_appConfig->loadModulesConfiguration('adminhtml.xml', $adminhtmlConfig);
            $this->_adminhtmlConfig = $adminhtmlConfig;

            if ($this->_app->useCache('config')) {
                $this->_app->saveCache($adminhtmlConfig->getXmlString(), $this->getCacheId(),
                    array(Mage_Core_Model_Config::CACHE_TAG));
            }
        }
    }

    /**
     * Retrieve base helper by module
     *
     * @param string $module
     * @return Mage_Core_Helper_Abstract
     */
    protected function _getHelper($module)
    {
        if (isset($this->_helpers[$module])) {
            return $this->_helpers[$module];
        }
        return Mage::helper($module);
    }

    /**
     * Retrieve xml config
     *
     * @return Varien_Simplexml_Config
     */
    public function getAdminhtmlConfig()
    {
        return $this->_adminhtmlConfig;
    }
}
