<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\View\Layout\File\Source\Override;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\View\Layout\File\Source\Override\Theme
     */
    private $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_directory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_fileFactory;

    protected function setUp()
    {
        $filesystem = $this->getMock('Magento\Filesystem', array('getDirectoryRead'), array(), '', false);
        $this->_directory = $this->getMock('Magento\Filesystem\Directory\Read', array(), array(), '', false);
        $this->_directory->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnArgument(0));

        $filesystem->expects($this->any())->method('getDirectoryRead')
            ->with($this->equalTo(\Magento\Filesystem::THEMES))
            ->will($this->returnValue($this->_directory));
        $this->_fileFactory = $this->getMock('Magento\View\Layout\File\Factory', array(), array(), '', false);
        $this->_model = new \Magento\View\Layout\File\Source\Override\Theme(
            $filesystem, $this->_fileFactory
        );
    }

    public function testGetFiles()
    {
        $grandparentTheme = $this->getMockForAbstractClass('Magento\View\Design\ThemeInterface');
        $grandparentTheme->expects($this->once())->method('getCode')->will($this->returnValue('grand_parent_theme'));

        $parentTheme = $this->getMockForAbstractClass('Magento\View\Design\ThemeInterface');
        $parentTheme->expects($this->once())->method('getCode')->will($this->returnValue('parent_theme'));
        $parentTheme->expects($this->once())->method('getParentTheme')->will($this->returnValue($grandparentTheme));

        $theme = $this->getMockForAbstractClass('Magento\View\Design\ThemeInterface');
        $theme->expects($this->once())->method('getFullPath')->will($this->returnValue('area/theme_path'));
        $theme->expects($this->once())->method('getParentTheme')->will($this->returnValue($parentTheme));

        $filePathOne = 'design/area/theme_path/Module_One/layout/override/theme/parent_theme/1.xml';
        $filePathTwo = 'design/area/theme_path/Module_Two/layout/override/theme/grand_parent_theme/2.xml';
        $this->_directory->expects($this->once())
            ->method('search')
            ->with($this->equalTo('area/theme_path/*_*/layout/override/theme/*/*.xml'))
            ->will($this->returnValue(array($filePathOne, $filePathTwo)));

        $fileOne = new \Magento\View\Layout\File('1.xml', 'Module_One', $parentTheme);
        $fileTwo = new \Magento\View\Layout\File('2.xml', 'Module_Two', $grandparentTheme);
        $this->_fileFactory
            ->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValueMap(array(
                array($filePathOne, 'Module_One', $parentTheme, $fileOne),
                array($filePathTwo, 'Module_Two', $grandparentTheme, $fileTwo),
            )))
        ;

        $this->assertSame(array($fileOne, $fileTwo), $this->_model->getFiles($theme));
    }

    public function testGetFilesWithPreset()
    {
        $grandparentTheme = $this->getMockForAbstractClass('Magento\View\Design\ThemeInterface');
        $grandparentTheme->expects($this->once())->method('getCode')->will($this->returnValue('grand_parent_theme'));

        $parentTheme = $this->getMockForAbstractClass('Magento\View\Design\ThemeInterface');
        $parentTheme->expects($this->once())->method('getCode')->will($this->returnValue('parent_theme'));
        $parentTheme->expects($this->once())->method('getParentTheme')->will($this->returnValue($grandparentTheme));

        $theme = $this->getMockForAbstractClass('Magento\View\Design\ThemeInterface');
        $theme->expects($this->once())->method('getFullPath')->will($this->returnValue('area/theme_path'));
        $theme->expects($this->once())->method('getParentTheme')->will($this->returnValue($parentTheme));

        $filePathOne = 'design/area/theme_path/Module_Two/layout/override/theme/grand_parent_theme/preset/3.xml';
        $this->_directory->expects($this->once())
            ->method('search')
            ->with('area/theme_path/*_*/layout/override/theme/*/preset/3.xml')
            ->will($this->returnValue(array($filePathOne)))
        ;

        $fileOne = new \Magento\View\Layout\File('3.xml', 'Module_Two', $grandparentTheme);
        $this->_fileFactory
            ->expects($this->once())
            ->method('create')
            ->with($filePathOne, 'Module_Two', $grandparentTheme)
            ->will($this->returnValue($fileOne))
        ;

        $this->assertSame(array($fileOne), $this->_model->getFiles($theme, 'preset/3'));
    }

    public function testGetFilesWrongAncestor()
    {
        $filePath = 'design/area/theme_path/Module_One/layout/override/theme/parent_theme/1.xml';
        $this->setExpectedException(
            'Magento\Exception',
            "Trying to override layout file '$filePath' for theme 'parent_theme'"
                . ", which is not ancestor of theme 'theme_path'"
        );

        $theme = $this->getMockForAbstractClass('Magento\View\Design\ThemeInterface');
        $theme->expects($this->once())->method('getFullPath')->will($this->returnValue('area/theme_path'));
        $theme->expects($this->once())->method('getParentTheme')->will($this->returnValue(null));
        $theme->expects($this->once())->method('getCode')->will($this->returnValue('theme_path'));

        $this->_directory->expects($this->once())
            ->method('search')
            ->with('area/theme_path/*_*/layout/override/theme/*/*.xml')
            ->will($this->returnValue(array($filePath)));

        $this->_model->getFiles($theme);
    }
}
