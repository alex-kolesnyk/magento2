<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Test\Integrity\Modular;

class DiConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Primary DI configs from app/etc
     * @var array
     */
    protected static $_primaryFiles = array();

    /**
     * Global DI configs from all modules
     * @var array
     */
    protected static $_moduleGlobalFiles = array();

    /**
     * Area DI configs from all modules
     * @var array
     */
    protected static $_moduleAreaFiles = array();

    protected function _prepareFiles()
    {
        //init primary configs
        /** @var $filesystem \Magento\Filesystem */
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Filesystem');

        $configPath = $filesystem->getPath(\Magento\Filesystem::APP) . '/etc/*/';
        self::$_primaryFiles = glob($configPath . '/di.xml');
        array_unshift(self::$_primaryFiles, $filesystem->getPath(\Magento\Filesystem::APP) . '/etc/di.xml');

        //init module global configs
        /** @var $modulesReader \Magento\Module\Dir\Reader */
        $modulesReader = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Module\Dir\Reader');
        self::$_moduleGlobalFiles = $modulesReader->getConfigurationFiles('di.xml');

        //init module area configs
        $areas = array('adminhtml', 'frontend');
        foreach ($areas as $area) {
            $moduleAreaFiles = $modulesReader->getConfigurationFiles($area . '/di.xml');
            self::$_moduleAreaFiles[$area] = $moduleAreaFiles;
        }
    }

    /**
     * @param string $file
     * @return void
     * @dataProvider linearFilesProvider
     */
    public function testDiConfigFileWithoutMerging($file)
    {
        /** @var \Magento\ObjectManager\Config\SchemaLocator $schemaLocator */
        $schemaLocator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\ObjectManager\Config\SchemaLocator');

        $dom = new \DOMDocument();
        $dom->load($file);
        if (!@$dom->schemaValidate($schemaLocator->getSchema())) {
            $this->fail('File ' . $file . ' has invalid xml structure.');
        }
    }

    public function linearFilesProvider()
    {
        if (empty(self::$_primaryFiles)) {
            $this->_prepareFiles();
        }

        $common = array_merge(self::$_primaryFiles, self::$_moduleGlobalFiles);

        foreach (self::$_moduleAreaFiles as $files) {
            $common = array_merge($common, $files);
        }

        $output = array();
        foreach ($common as $file) {
            $output[$file] = array($file);
        }

        return $output;
    }

    /**
     * @param array $files
     * @dataProvider mixedFilesProvider
     */
    public function testMergedDiConfig(array $files)
    {
        $mapperMock = $this->getMock('Magento\ObjectManager\Config\Mapper\Dom', array(), array(), '', false);
        $fileResolverMock = $this->getMock('Magento\Config\FileResolverInterface');
        $fileResolverMock->expects($this->any())->method('read')->will($this->returnValue($files));
        $validationStateMock = $this->getMock('Magento\Config\ValidationStateInterface');
        $validationStateMock->expects($this->any())->method('isValidated')->will($this->returnValue(true));

        /** @var \Magento\ObjectManager\Config\SchemaLocator $schemaLocator */
        $schemaLocator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\ObjectManager\Config\SchemaLocator');

        new \Magento\ObjectManager\Config\Reader\Dom(
            $fileResolverMock, $mapperMock, $schemaLocator, $validationStateMock
        );
    }

    public function mixedFilesProvider()
    {
        if (empty(self::$_primaryFiles)) {
            $this->_prepareFiles();
        }
        foreach (self::$_primaryFiles as $file) {
            $primaryFiles[$file] = array(array($file));
        }
        $primaryFiles['all primary config files'] = array(self::$_primaryFiles);

        foreach (self::$_moduleGlobalFiles as $file) {
            $moduleFiles[$file] = array(array($file));
        }
        $moduleFiles['all module global config files'] = array(self::$_moduleGlobalFiles);

        $areaFiles = array();
        foreach (self::$_moduleAreaFiles as $area => $files) {
            foreach ($files as $file) {
                $areaFiles[$file] = array(array($file));
            }
            $areaFiles["all $area config files"] = array(self::$_moduleAreaFiles[$area]);
        }

        return $primaryFiles + $moduleFiles + $areaFiles;
    }
}
