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

namespace Magento\Core\Helper;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    const ROOT = '/zzz';
    const APP = '/zzz/qqq';
    const MODULES = '/zzz/qqq/code00';
    const THEMES = '/zzz/qqq/design00';
    const PUB_LIB = '/zzz/qqq/js00';

    /**
     * @dataProvider getSafePathDataProvider
     * @param string $filePath
     * @param string $basePath
     * @param string $expectedResult
     */
    public function testGetSafePath($filePath, $basePath, $expectedResult)
    {
        /** @var $dirs \Magento\Core\Model\Dir */
        $dirs = $this->getMock('Magento\Core\Model\Dir', null, array(), '', false);

        /** @var $layoutMergeFactory \Magento\Core\Model\Layout\MergeFactory */
        $layoutMergeFactory = $this->getMock('Magento\Core\Model\Layout\MergeFactory', array('create'),
            array(), '', false
        );

        /** @var $themeCollection \Magento\Core\Model\Resource\Theme\Collection */
        $themeCollection = $this->getMock('Magento\Core\Model\Resource\Theme\Collection', null, array(), '', false);

        /** @var $context \Magento\Core\Helper\Context */
        $context = $this->getMock('Magento\Core\Helper\Context', null, array(), '', false);

        $fileSystem = $this->getMockBuilder('Magento\Core\Model\View\FileSystem')->disableOriginalConstructor()
            ->getMock();

        $helper = new \Magento\Core\Helper\Theme(
            $context,
            $dirs,
            $layoutMergeFactory,
            $themeCollection,
            $fileSystem
        );

        $result = $helper->getSafePath($filePath, $basePath);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getSafePathDataProvider()
    {
        return array(
            array('/1/2/3/4/5/6.test', '/1/2/3/', '4/5/6.test'),
            array('/1/2/3/4/5/6.test', '/1/2/3', '4/5/6.test'),
        );
    }

    /**
     * @dataProvider getCssFilesDataProvider
     * @param string $layoutStr
     * @param array $expectedResult
     */
    public function testGetCssFiles($layoutStr, $expectedResult)
    {
        // 1. Set data
        $themeId = 123;
        $themeArea = 'area123';

        // 2. Get theme model
        $theme = $this->_getTheme($themeId, $themeArea);

        // 3. Get dirs model
        $dirs = $this->_getDirs();

        // 4. Get layout merge model and factory
        $layoutMergeFactory = $this->_getLayoutMergeFactory($layoutStr);

        // 5.
        /** @var $themeCollection \Magento\Core\Model\Resource\Theme\Collection */
        $themeCollection = $this->getMock('Magento\Core\Model\Resource\Theme\Collection', null, array(), '', false);

        // 6.
        /** @var $context \Magento\Core\Helper\Context */
        $context = $this->getMock('Magento\Core\Helper\Context', null, array(), '', false);

        // 7. Get view file system model mock
        $params = array(
            'area'       => $themeArea,
            'themeModel' => $theme,
            'skipProxy'  => true
        );
        $map = array(
            array('test1.css', $params, '/zzz/qqq/test1.css'),
            array('test2.css', $params, '/zzz/qqq/test2.css'),
            array('Magento_Core::test3.css', $params, '/zzz/qqq/test3.css'),
            array('test4.css', $params, '/zzz/qqq/test4.css'),
            array('test21.css', $params, '/zzz/qqq/test21.css'),
            array('test22.css', $params, '/zzz/qqq/test22.css'),
            array('Magento_Core::test23.css', $params, '/zzz/qqq/test23.css'),
            array('test24.css', $params, '/zzz/qqq/test24.css'),
        );
        $fileSystem = $this->_getFileSystem($map);

        // 8. Run tested method
        $helper = new \Magento\Core\Helper\Theme(
            $context,
            $dirs,
            $layoutMergeFactory,
            $themeCollection,
            $fileSystem
        );
        $result = $helper->getCssFiles($theme);
        // 9. Compare actual result with expected data
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCssFilesDataProvider()
    {
        return array(
            array(
                '<block class="Magento\Page\Block\Html\Head" name="head">
                    <block class="Magento\Page\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">test1.css</argument></arguments>
                    </block>
                </block>',
                array(
                    'test1.css' => array(
                        'id'       => 'test1.css',
                        'path'     => '/zzz/qqq/test1.css',
                        'safePath' => 'qqq/test1.css'
                    )
                )
            ),
            array(
                '<block class="Magento\Page\Block\Html\Head" name="head">
                    <block class="Magento\Page\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments>
                            <argument name="file" xsi:type="string">Magento_Core::test3.css</argument>
                        </arguments>
                    </block>
                </block>',
                array(
                    'Magento_Core::test3.css' => array(
                        'id'       => 'Magento_Core::test3.css',
                        'path'     => '/zzz/qqq/test3.css',
                        'safePath' => 'qqq/test3.css'
                    ),
                )
            ),
            array(
                '<referenceBlock name="head">
                    <block class="Magento\Page\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">test21.css</argument></arguments>
                    </block>
                </referenceBlock>',
                array(
                    'test21.css' => array(
                        'id'       => 'test21.css',
                        'path'     => '/zzz/qqq/test21.css',
                        'safePath' => 'qqq/test21.css'
                    ),
                )
            ),
            array(
                '<referenceBlock name="head">
                    <block class="Magento\Page\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments>
                            <argument name="file" xsi:type="string">Magento_Core::test23.css</argument>
                        </arguments>
                    </block>
                </referenceBlock>',
                array(
                    'Magento_Core::test23.css' => array(
                        'id'       => 'Magento_Core::test23.css',
                        'path'     => '/zzz/qqq/test23.css',
                        'safePath' => 'qqq/test23.css'
                    ),
                )
            ),
            array(
                '<block type="Some_Block_Class">
                    <block class="Magento\Page\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments>
                            <argument name="file" xsi:type="string">Magento_Core::test23.css</argument>
                        </arguments>
                    </block>
                </block>',
                array(),

            ),
            array(
                '<block type="Some_Block_Class">
                    <block class="Magento\Page\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments>
                            <argument name="file" xsi:type="string">Magento_Core::test23.css</argument>
                        </arguments>
                    </block>
                </block>',
                array(),
            ),
            array(
                '<referenceBlock name="some_block_name">
                    <block class="Magento\Page\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">test23.css</argument></arguments>
                    </block>
                </referenceBlock>',
                array(),
            ),
            array(
                '<referenceBlock name="some_block_name">
                    <block class="Magento\Page\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments>
                            <argument name="file" xsi:type="string">Magento_Core::test23.css</argument>
                        </arguments>
                    </block>
                </referenceBlock>',
                array(),
            ),
            array(
                '<block class="Magento\Page\Block\Html\Head" name="head">
                    <block class="Magento\Page\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">test.css</argument></arguments>
                    </block>
                    <block class="Magento\Page\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments>
                            <argument name="file" xsi:type="string">Magento_Core::test.css</argument>
                        </arguments>
                    </block>
                </block>
                <referenceBlock name="head">
                    <block class="Magento\Page\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">testh.css</argument></arguments>
                    </block>
                    <block class="Magento\Page\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">Magento_Core::test.css</argument></arguments>
                    </block>
                </referenceBlock>
                <block type="Some_Block_Class">
                    <block class="Magento\Page\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">testa.css</argument></arguments>
                    </block>
                    <block class="Magento\Page\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments>
                            <argument name="file" xsi:type="string">Magento_Core::testa.css</argument>
                        </arguments>
                    </block>
                </block>
                <referenceBlock name="some_block_name">
                    <block class="Magento\Page\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">testb.css</argument></arguments>
                    </block>
                    <block class="Magento\Page\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments>
                            <argument name="file" xsi:type="string">Magento_Core::testb.css</argument>
                        </arguments>
                    </block>
                </referenceBlock>',
                array(
                    'testh.css' => array(
                        'id' => 'testh.css',
                        'path' => '',
                        'safePath' => '',
                    ),
                    'Magento_Core::test.css' => array(
                        'id' => 'Magento_Core::test.css',
                        'path' => '',
                        'safePath' => '',
                    ),
                    'test.css' => array(
                        'id' => 'test.css',
                        'path' => '',
                        'safePath' => '',
                    ),
                ),
            ),
        );
    }

    /**
     * depends testGetCssFiles
     * @dataProvider getGroupedCssFilesDataProvider
     * @param array $files
     * @param array $expectedResult
     */
    public function testGetGroupedCssFiles($files, $expectedResult)
    {
        $helper = $this->_getHelper($files);

        $theme = 'anything';
        $result = $helper->getGroupedCssFiles($theme);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getGroupedCssFilesDataProvider()
    {
        $item11 = array(
            'path'     => '/zzz/qqq/design00/area11/vendor11_theme11/test11.test',
            'safePath' => 'design00/area11/vendor11_theme11/test11.test'
        );
        $item12 = array(
            'path'     => '/zzz/qqq/design00/area12/vendor12_theme12/test12.test',
            'safePath' => 'design00/area12/vendor12_theme12/test12.test'
        );
        $item13 = array(
            'path'     => '/zzz/qqq/design00/area13/vendor13_theme13/test13.test',
            'safePath' => 'design00/area13/vendor13_theme13/test13.test'
        );

        $item21 = array(
            'path'     => '/zzz/qqq/code00/Magento_Core00/test21.test',
            'safePath' => 'code00/Magento_Core00/test21.test'
        );
        $item31 = array(
            'path'     => '/zzz/qqq/js00/some_path/test31.test',
            'safePath' => 'js00/some_path/test31.test'
        );
        $groups11 = array(
            '"11" Theme files' => array(
                array(
                    'path'     => '/zzz/qqq/design00/area11/vendor11_theme11/test11.test',
                    'safePath' => 'design00/area11/vendor11_theme11/test11.test'
                ),
            )
        );
        $groups12 = array(
            '"12" Theme files' => array(
                array(
                    'path'     => '/zzz/qqq/design00/area12/vendor12_theme12/test12.test',
                    'safePath' => 'design00/area12/vendor12_theme12/test12.test'
                ),
            )
        );
        $groups13 = array(
            '"13" Theme files' => array(
                array(
                    'path'     => '/zzz/qqq/design00/area13/vendor13_theme13/test13.test',
                    'safePath' => 'design00/area13/vendor13_theme13/test13.test'
                ),
            )
        );
        $groups1 = array(
            '"11" Theme files' => array(
                array(
                    'path'     => '/zzz/qqq/design00/area11/vendor11_theme11/test11.test',
                    'safePath' => 'design00/area11/vendor11_theme11/test11.test'
                ),
            ),
            '"12" Theme files' => array(
                array(
                    'path'     => '/zzz/qqq/design00/area12/vendor12_theme12/test12.test',
                    'safePath' => 'design00/area12/vendor12_theme12/test12.test'
                ),
            ),
            '"13" Theme files' => array(
                array(
                    'path'     => '/zzz/qqq/design00/area13/vendor13_theme13/test13.test',
                    'safePath' => 'design00/area13/vendor13_theme13/test13.test'
                ),
            )
        );
        $groups21 = array(
            'Framework files' => array(
                array(
                    'path'     => '/zzz/qqq/code00/Magento_Core00/test21.test',
                    'safePath' => 'code00/Magento_Core00/test21.test'
                ),
            )
        );
        $groups31 = array(
            'Library files' => array(
                array(
                    'path'     => '/zzz/qqq/js00/some_path/test31.test',
                    'safePath' => 'js00/some_path/test31.test'
                ),
            )
        );
        return array(
            array(array($item11), $groups11),
            array(array($item12), $groups12),
            array(array($item13), $groups13),
            array(array($item11, $item12, $item13), $groups1),
            array(array($item21), $groups21),
            array(array($item31), $groups31),
            array(
                array($item11, $item12, $item13, $item21, $item31),
                array_merge($groups1, $groups21, $groups31)
            ),
        );
    }

    /**
     * depends testGetCssFiles
     * @expectedException \LogicException
     * @expectedExceptionMessage Invalid view file directory "some_path/test.test"
     */
    public function testGetGroupedCssFilesException()
    {
        $files = array(array(
            'path'     => '/zzz/some_path/test.test',
            'safePath' => 'some_path/test.test'
        ));

        $helper = $this->_getHelper($files);

        $theme = 'anything';
        $helper->getGroupedCssFiles($theme);
    }

    /**
     * @param int $themeId
     * @param string $themeArea
     * @return \Magento\Core\Model\Theme|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getTheme($themeId, $themeArea)
    {
        /** @var $theme \Magento\Core\Model\Theme|\PHPUnit_Framework_MockObject_MockObject */
        $theme = $this->getMock(
            'Magento\Core\Model\Theme',
            array('getThemeId', 'getArea', 'getThemeTitle', '__wakeup'),
            array(),
            '',
            false
        );
        $theme->expects($this->any())
            ->method('getThemeId')
            ->will($this->returnValue($themeId));
        $theme->expects($this->any())
            ->method('getArea')
            ->will($this->returnValue($themeArea));
        $theme->expects($this->any())
            ->method('getThemeTitle')
            ->will($this->returnValue($themeId));

        return $theme;
    }

    /**
     * @param array $map
     * @return \Magento\Core\Model\View\FileSystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getFileSystem($map)
    {
        /** @var $fileSystem \Magento\Core\Model\View\FileSystem|\PHPUnit_Framework_MockObject_MockObject */
        $fileSystem = $this->getMockBuilder('Magento\Core\Model\View\FileSystem', array())
            ->disableOriginalConstructor()->getMock();
        $fileSystem->expects($this->any())
            ->method('getViewFile')
            ->will($this->returnValueMap($map));

        return $fileSystem;
    }

    /**
     * @param string $layoutStr
     * @return \Magento\Core\Model\Layout\MergeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getLayoutMergeFactory($layoutStr)
    {
        /** @var $layoutMerge \Magento\Core\Model\Layout\Merge */
        $layoutMerge = $this->getMock('Magento\Core\Model\Layout\Merge',
            array('getFileLayoutUpdatesXml'), array(), '', false
        );
        $xml = '<layouts xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . $layoutStr . '</layouts>';
        $layoutElement = simplexml_load_string($xml);
        $layoutMerge->expects($this->any())
            ->method('getFileLayoutUpdatesXml')
            ->will($this->returnValue($layoutElement));

        /** @var $layoutMergeFactory \Magento\Core\Model\Layout\MergeFactory */
        $layoutMergeFactory = $this->getMock('Magento\Core\Model\Layout\MergeFactory',
            array('create'), array(), '', false
        );
        $layoutMergeFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($layoutMerge));

        return $layoutMergeFactory;
    }

    /**
     * @return \Magento\Core\Model\Dir|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getDirs()
    {
        /** @var $dirs \Magento\Core\Model\Dir */
        $dirs = $this->getMock('Magento\Core\Model\Dir', array('getDir'), array(), '', false);
        $dirs->expects($this->any())
            ->method('getDir')
            ->will($this->returnValueMap(array(
                array(\Magento\Core\Model\Dir::ROOT, self::ROOT),
                array(\Magento\Core\Model\Dir::APP, self::APP),
                array(\Magento\Core\Model\Dir::MODULES, self::MODULES),
                array(\Magento\Core\Model\Dir::THEMES, self::THEMES),
                array(\Magento\Core\Model\Dir::PUB_LIB, self::PUB_LIB),
            )));

        return $dirs;
    }

    /**
     * @return \Magento\Core\Model\Resource\Theme\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getThemeCollection()
    {
        $theme11 = $this->_getTheme('11', 'area11');
        $theme12 = $this->_getTheme('12', 'area12');
        $theme13 = $this->_getTheme('13', 'area13');

        /** @var $themeCollection \Magento\Core\Model\Resource\Theme\Collection */
        $themeCollection = $this->getMock('Magento\Core\Model\Resource\Theme\Collection',
            array('getThemeByFullPath'), array(), '', false
        );
        $themeCollection->expects($this->any())
            ->method('getThemeByFullPath')
            ->will($this->returnValueMap(array(
                array('area11/vendor11_theme11', $theme11),
                array('area12/vendor12_theme12', $theme12),
                array('area13/vendor13_theme13', $theme13),
            )));

        return $themeCollection;
    }

    /**
     * @param array $files
     * @return \Magento\Core\Helper\Theme|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getHelper($files)
    {
        // Get theme collection
        $themeCollection = $this->_getThemeCollection();

        // 3. Get Design Package model

        // 4. Get dirs model
        $dirs = $this->_getDirs();

        // 5. Get layout merge model and factory
        /** @var $layoutMergeFactory \Magento\Core\Model\Layout\MergeFactory|\PHPUnit_Framework_MockObject_MockObject */
        $layoutMergeFactory = $this->getMock('Magento\Core\Model\Layout\MergeFactory',
            array('create'), array(), '', false
        );

        /** @var $context \Magento\Core\Helper\Context */
        $context = $this->getMock('Magento\Core\Helper\Context', null, array(), '', false);

        /** @var $fileSystem \Magento\Core\Model\View\FileSystem|\PHPUnit_Framework_MockObject_MockObject */
        $fileSystem = $this->getMockBuilder('Magento\Core\Model\View\FileSystem', array())
            ->disableOriginalConstructor()->getMock();

        /** @var $helper \Magento\Core\Helper\Theme|\PHPUnit_Framework_MockObject_MockObject */
        $helper = $this->getMock('Magento\Core\Helper\Theme', array('getCssFiles'), array(
            $context, $dirs, $layoutMergeFactory, $themeCollection, $fileSystem
        ));
        $helper->expects($this->once())
            ->method('getCssFiles')
            ->will($this->returnValue($files));

        return $helper;
    }
}
