<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test that Design Package delegates fallback resolution to a Fallback model
 */
namespace Magento\View\Design\FileResolution\Strategy;

use Magento\Filesystem\DirectoryList;

/**
 * Fallback Test
 *
 * @package Magento\View
 */
class FallbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\View\Design\Fallback\Factory
     */
    protected $fallbackFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fallbackFile;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fallbackLocale;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fallbackViewFile;

    /**
     * @var \Magento\View\Design\ThemeInterface
     */
    protected $theme;

    protected function setUp()
    {
        $this->fallbackFile = $this->getMockForAbstractClass('Magento\View\Design\Fallback\Rule\RuleInterface');
        $this->fallbackLocale = $this->getMockForAbstractClass(
            'Magento\View\Design\Fallback\Rule\RuleInterface'
        );
        $this->fallbackViewFile = $this->getMockForAbstractClass(
            'Magento\View\Design\Fallback\Rule\RuleInterface'
        );

        $this->fallbackFactory = $this->getMock(
            'Magento\View\Design\Fallback\Factory',
            array('createLocaleFileRule', 'createFileRule', 'createViewFileRule'),
            array($this->getMock('Magento\Filesystem', array(), array(), '', false))
        );
        $this->fallbackFactory
            ->expects($this->any())->method('createLocaleFileRule')->will($this->returnValue($this->fallbackLocale));
        $this->fallbackFactory
            ->expects($this->any())->method('createFileRule')->will($this->returnValue($this->fallbackFile));
        $this->fallbackFactory
            ->expects($this->any())->method('createViewFileRule')->will($this->returnValue($this->fallbackViewFile));

        $this->theme = $this->getMock('Magento\View\Design\ThemeInterface', array(), array(), '', false);
    }

    protected function tearDown()
    {
        $this->fallbackFactory = null;
        $this->fallbackFile = null;
        $this->fallbackLocale = null;
        $this->fallbackViewFile = null;
        $this->theme = null;
    }

    /**
     * @dataProvider getFileDataProvider
     */
    public function testGetFile($fullModuleName, $namespace, $module, $targetFile, $expectedFileName)
    {
        $filesystem = $this->getFileSystemMock($targetFile);

        $fallback = new Fallback(
            $filesystem,
            $this->fallbackFactory
        );

        $params = array('area' => 'area', 'theme' => $this->theme, 'namespace' => $namespace, 'module' => $module);

        $this->fallbackFile->expects($this->once())
            ->method('getPatternDirs')
            ->with($params)
            ->will($this->returnValue(array('found_folder', 'not_found_folder')));

        $filename = $fallback->getFile('area', $this->theme, 'file.txt', $fullModuleName);

        $this->assertEquals($expectedFileName, $filename);
    }

    /**
     * @return array
     */
    public function getFileDataProvider()
    {
        return array(
            'no module, file found' => array(
                null,
                null,
                null,
                'found_folder/file.txt',
                'found_folder/file.txt',
            ),
            'module, file found' => array(
                'Namespace_Module',
                'Namespace',
                'Module',
                'found_folder/file.txt',
                'found_folder/file.txt',
            ),
            'no module, file not found' => array(
                null,
                null,
                null,
                null,
                'not_found_folder/file.txt',
            ),
            'module, file not found' => array(
                'Namespace_Module',
                'Namespace',
                'Module',
                null,
                'not_found_folder/file.txt',
            ),
        );
    }

    /**
     * @dataProvider getLocaleFileDataProvider
     */
    public function testGetLocaleFile($targetFile, $expectedFileName)
    {
        $filesystem = $this->getFileSystemMock($targetFile);

        $fallback = new Fallback(
            $filesystem,
            $this->fallbackFactory
        );

        $params = array('area' => 'area', 'theme' => $this->theme, 'locale' => 'locale');

        $this->fallbackLocale->expects($this->once())
            ->method('getPatternDirs')
            ->with($params)
            ->will($this->returnValue(array('found_folder', 'not_found_folder')));

        $filename = $fallback->getLocaleFile('area', $this->theme, 'locale', 'file.txt');

        $this->assertEquals($expectedFileName, $filename);
    }

    /**
     * @return array
     */
    public function getLocaleFileDataProvider()
    {
        return array(
            'file found' => array(
                'found_folder/file.txt',
                'found_folder/file.txt',
            ),
            'file not found' => array(
                null,
                'not_found_folder/file.txt',
            )
        );
    }

    /**
     * @dataProvider getFileDataProvider
     */
    public function testGetViewFile($fullModuleName, $namespace, $module, $targetFile, $expectedFileName)
    {
        $filesystem = $this->getFileSystemMock($targetFile);

        $fallback = new Fallback(
            $filesystem,
            $this->fallbackFactory
        );

        $params = array('area' => 'area', 'theme' => $this->theme, 'namespace' => $namespace, 'module' => $module,
            'locale' => 'locale');

        $this->fallbackViewFile->expects($this->once())
            ->method('getPatternDirs')
            ->with($params)
            ->will($this->returnValue(array('found_folder', 'not_found_folder')));

        $filename = $fallback->getViewFile('area', $this->theme, 'locale', 'file.txt', $fullModuleName);

        $this->assertEquals($expectedFileName, $filename);
    }

    /**
     * @param string $targetFile
     * @return \Magento\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFileSystemMock($targetFile)
    {
        $directoryMock = $this->getMock(
            'Magento\Filesystem\Directory\Read',
            array('isExist', 'getRelativePath'), array(), '', false
        );
        $directoryMock->expects($this->any())
            ->method('getRelativePath')
            ->will($this->returnArgument(0));
        $directoryMock->expects($this->any())
            ->method('isExist')
            ->will(
                $this->returnCallback(
                    function ($tryFile) use ($targetFile) {
                        return ($tryFile == $targetFile);
                    }
                )
            );
        $filesystem = $this->getMock('Magento\Filesystem', array('getDirectoryRead', '__wakeup'), array(), '', false);
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::THEMES)
            ->will($this->returnValue($directoryMock));

        return $filesystem;
    }
}
