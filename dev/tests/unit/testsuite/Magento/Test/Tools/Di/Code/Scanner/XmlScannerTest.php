<?php
/**
 * {license_notice}
 *
 * @copyright  {copyright}
 * @license    {license_link}
 */

namespace Magento\Test\Tools\Di\Code\Scanner;

class XmlScannerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Di\Code\Scanner\XmlScanner
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_testDir;

    /**
     * @var array
     */
    protected $_testFiles = array();

    protected function setUp()
    {
        $this->_model = new \Magento\Tools\Di\Code\Scanner\XmlScanner();
        $this->_testDir = str_replace('\\', '/', realpath(__DIR__ . '/../../') . '/_files');
        $this->_testFiles =  array(
            $this->_testDir . '/app/code/Magento/SomeModule/etc/adminhtml/system.xml',
            $this->_testDir . '/app/code/Magento/SomeModule/etc/di.xml',
            $this->_testDir . '/app/code/Magento/SomeModule/view/frontend/default.xml',
            $this->_testDir . '/app/etc/di/config.xml'

        );
    }

    public function testCollectEntities()
    {
        $actual = $this->_model->collectEntities($this->_testFiles);
        $expected = array(
            'Magento\Backend\Block\System\Config\Form\Fieldset\Modules\DisableOutput\Proxy',
            'Magento\Core\Model\App\Proxy',
            'Magento\Core\Model\Cache\Proxy',
            'Magento\Backend\Block\Menu\Proxy',
            'Magento\Core\Model\StoreManager\Proxy',
            'Magento\Core\Model\Layout\Factory',
        );
        $this->assertEquals($expected, $actual);
    }
}
