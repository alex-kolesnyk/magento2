<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_SystemConfiguration_Helper extends Mage_Selenium_TestCase
{
    /**
     * System Configuration
     *
     * @param array|string $parameters
     */
    public function configure($parameters)
    {
        if (is_string($parameters)) {
            $elements = explode('/', $parameters);
            $fileName = (count($elements) > 1) ? array_shift($elements) : '';
            $parameters = $this->loadDataSet($fileName, implode('/', $elements));
        }
        $chooseScope = (isset($parameters['configuration_scope'])) ? $parameters['configuration_scope'] : null;
        if ($chooseScope) {
            $this->selectStoreScope('dropdown', 'current_configuration_scope', $chooseScope);
        }
        foreach ($parameters as $value) {
            if (!is_array($value)) {
                continue;
            }
            $tab = (isset($value['tab_name'])) ? $value['tab_name'] : null;
            $settings = (isset($value['configuration'])) ? $value['configuration'] : null;
            if ($tab) {
                $this->openConfigurationTab($tab);
                foreach ($settings as $fieldsetName => $fieldsetData) {
                    $fieldsetForm = $this->getElement($this->_getControlXpath('fieldset', $fieldsetName));
                    if ($fieldsetForm->name() == 'fieldset') {
                        $fieldsetLink = $this->getElement($this->_getControlXpath('link', $fieldsetName . '_link'));
                        if (strpos($fieldsetLink->attribute('class'), 'open') === false) {
                            $fieldsetLink->click();
                        }
                    }
                    $this->fillFieldset($fieldsetData, $fieldsetName);
                }
                $this->saveForm('save_config');
                $this->assertMessagePresent('success', 'success_saved_config');
                foreach ($settings as $fieldsetData) {
                    $this->verifyForm($fieldsetData, $tab);
                }
                $this->verifyForm($settings, $tab);
                if ($this->getParsedMessages('verification')) {
                    foreach ($this->getParsedMessages('verification') as $key => $errorMessage) {
                        if (preg_match('#(\'all\' \!\=)|(\!\= \'\*\*)|(\'all\')#i', $errorMessage)) {
                            unset(self::$_messages['verification'][$key]);
                        }
                    }
                    $this->assertEmptyVerificationErrors();
                }
            }
        }
    }

    /**
     * Open tab on Configuration page
     *
     * @param string $tab
     */
    public function openConfigurationTab($tab)
    {
        if (!$this->controlIsPresent('tab', $tab)) {
            $this->fail("Current location url: '" . $this->url() . "'\nCurrent page: '" . $this->getCurrentPage()
                        . "'\nTab '$tab' is not present on the page");
        }
        $this->defineParameters('tab', $tab, 'href');
        $this->clickControl('tab', $tab);
    }

    /**
     * Define Url Parameters for System Configuration page
     *
     * @param string $controlType
     * @param string $controlName
     * @param string $attribute
     *
     * @return void
     */
    public function defineParameters($controlType, $controlName, $attribute)
    {
        $params = $this->getControlAttribute($controlType, $controlName, $attribute);
        $params = explode('/', $params);
        foreach ($params as $key => $value) {
            if ($value == 'section' && isset($params[$key + 1])) {
                $this->addParameter('tabName', $params[$key + 1]);
            }
            if ($value == 'website' && isset($params[$key + 1])) {
                $this->addParameter('webSite', $params[$key + 1]);
            }
            if ($value == 'store' && isset($params[$key + 1])) {
                $this->addParameter('storeName', $params[$key + 1]);
            }
        }
    }

    /**
     * Enable/Disable option 'Use Secure URLs in Admin/Frontend'
     *
     * @param string $path
     * @param string $useSecure
     */
    public function useHttps($path = 'admin', $useSecure = 'Yes')
    {
        $this->admin('system_configuration');
        $this->openConfigurationTab('general_web');
        $secureBaseUrl = $this->getControlAttribute('field', 'secure_base_url', 'value');
        $data = array('secure_base_url'             => preg_replace('/http(s)?/', 'https', $secureBaseUrl),
                      'use_secure_urls_in_' . $path => ucwords(strtolower($useSecure)));
        $this->fillForm($data, 'general_web');
        $this->clickButton('save_config');
        $this->assertTrue($this->verifyForm($data, 'general_web'), $this->getParsedMessages());
    }

    /**
     * @param $parameters
     */
    public function configurePaypal($parameters)
    {
        $this->configure($parameters);
    }

}