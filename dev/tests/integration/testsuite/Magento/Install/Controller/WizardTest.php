<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Install
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Install\Controller;

class WizardTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var string
     */
    protected static $_tmpDir;

    /**
     * @var array
     */
    protected static $_params = array();

    public static function setUpBeforeClass()
    {
        $tmpDir =
            \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInstallDir()
                . DIRECTORY_SEPARATOR . 'WizardTest';
        if (is_file($tmpDir)) {
            unlink($tmpDir);
        } elseif (is_dir($tmpDir)) {
            \Magento\Io\File::rmdirRecursive($tmpDir);
        }
        // deliberately create a file instead of directory to emulate broken access to static directory
        touch($tmpDir);
        self::$_tmpDir = $tmpDir;
    }

    public function testPreDispatch()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->configure(array(
            'preferences' => array(
                'Magento\App\RequestInterface' => 'Magento\TestFramework\Request',
                'Magento\App\Response\Http' => 'Magento\TestFramework\Response'
            )
        ));
        /** @var $appState \Magento\App\State */
        $appState = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\State');
        $appState->setInstallDate(false);
        $this->dispatch('install/wizard');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $appState->setInstallDate(date('r', strtotime('now')));
    }

    /**
     * @param string $action
     * @dataProvider actionsDataProvider
     * @expectedException \Magento\BootstrapException
     */
    public function testPreDispatchImpossibleToRenderPage($action)
    {
        $params = self::$_params;
        $params[\Magento\App\Dir::PARAM_APP_DIRS][\Magento\App\Dir::STATIC_VIEW] = self::$_tmpDir;
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize($params);
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->configure(array(
            'preferences' => array(
                'Magento\App\RequestInterface' => 'Magento\TestFramework\Request',
                'Magento\App\Response\Http' => 'Magento\TestFramework\Response'
            )
        ));
        $this->dispatch("install/wizard/{$action}");
    }

    /**
     * @return array
     */
    public function actionsDataProvider()
    {
        return array(
            array('index'),
            array('begin'),
            array('beginPost'),
            array('locale'),
            array('localeChange'),
            array('localePost'),
            array('download'),
            array('downloadPost'),
            array('downloadAuto'),
            array('install'),
            array('downloadManual'),
            array('config'),
            array('configPost'),
            array('installDb'),
            array('administrator'),
            array('administratorPost'),
            array('end'),
        );
    }
}
