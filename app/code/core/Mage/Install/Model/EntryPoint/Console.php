<?php
/**
 * Console entry point
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Install_Model_EntryPoint_Console extends Mage_Core_Model_EntryPointAbstract
{
    /**
     * @param string $baseDir
     * @param array $params
     */
    public function __construct($baseDir, array $params = array())
    {
        $this->_params = $this->_buildInitParams($params);
        parent::__construct($baseDir, $this->_params);
    }

    /**
     * Customize application init parameters
     *
     * @param array $args
     * @return array
     */
    protected function _buildInitParams(array $args)
    {
        if (!empty($args[Mage_Install_Model_Installer_Console::OPTION_URIS])) {
            $args[Mage_Core_Model_App::INIT_OPTION_URIS] =
                unserialize(base64_decode($args[Mage_Install_Model_Installer_Console::OPTION_URIS]));
        }
        if (!empty($args[Mage_Install_Model_Installer_Console::OPTION_DIRS])) {
            $args[Mage_Core_Model_App::INIT_OPTION_DIRS] =
                unserialize(base64_decode($args[Mage_Install_Model_Installer_Console::OPTION_DIRS]));
        }
        return $args;
    }

    /**
     * Run http application
     */
    public function processRequest()
    {
        /**
         * @var $installer Mage_Install_Model_Installer_Console
         */
        $installer = $this->_objectManager->get(
            'Mage_Install_Model_Installer_Console',
            array('installArgs' => $this->_params)
        );
        if (false !== $this->_getParam('show_locales', false)) {
            var_export($installer->getAvailableLocales());
        } else if (false !== $this->_getParam('show_currencies', false)) {
            var_export($installer->getAvailableCurrencies());
        } else if (false !== $this->_getParam('show_timezones', false)) {
            var_export($installer->getAvailableTimezones());
        } else if (false !== $this->_getParam('show_install_options', false)) {
            var_export($installer->getAvailableInstallOptions());
        } else {
            if (false !== $this->_getParam('config', false) && file_exists($this->_params['config'])) {
                $config = (array) include($this->_params['config']);
                $this->_params = array_merge((array)$config, $this->_params);
            }
            $isUninstallMode = $this->_getParam('uninstall', false);
            if ($isUninstallMode) {
                $result = $installer->uninstall();
            } else {
                $result = $installer->install($this->_params);
            }
            if (!$installer->hasErrors()) {
                if ($isUninstallMode) {
                    $msg = $result ?
                        'Uninstalled successfully' :
                        'Ignoring attempt to uninstall non-installed application';
                } else {
                    $msg = 'Installed successfully' . ($result ? ' (encryption key "' . $result . '")' : '');
                }
                echo $msg . PHP_EOL;
            } else {
                echo implode(PHP_EOL, $installer->getErrors()) . PHP_EOL;
                exit(1);
            }
        }
    }
}
