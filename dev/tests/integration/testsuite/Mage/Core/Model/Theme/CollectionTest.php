<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test for filesystem themes collection
 */
class Mage_Core_Model_Theme_CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Theme_Collection
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = Mage::getModel('Mage_Core_Model_Theme_Collection');
        $this->_model->setBaseDir(dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files'. DIRECTORY_SEPARATOR . 'design');
    }

    /**
     * Test load themes collection from filesystem
     *
     * @magentoAppIsolation enabled
     */
    public function testLoadThemesFromFileSystem()
    {
        $pathPattern = implode(DS, array('frontend', 'default', '*', 'theme.xml'));
        $this->_model->addTargetPattern($pathPattern);
        $this->assertEquals(2, count($this->_model));
    }

    /**
     * Load from configuration
     *
     * @dataProvider expectedThemeDataFromConfiguration
     */
    public function testLoadFromConfiguration($themePath, $expectedData)
    {
        $theme = $this->_model->addTargetPattern($themePath)->getFirstItem();
        $this->assertEquals($expectedData, $theme->getData());
    }

    /**
     * Expected theme data from configuration
     *
     * @return array
     */
    public function expectedThemeDataFromConfiguration()
    {
        $designPath = implode(DIRECTORY_SEPARATOR, array(
            dirname(__DIR__), '_files', 'design', 'frontend', 'default', 'default'
        ));
        return array(
            array(
                'themePath'    => implode(DIRECTORY_SEPARATOR, array('frontend', 'default', 'default', 'theme.xml')),
                'expectedData' => array(
                    'area'                 => 'frontend',
                    'theme_title'          => 'Default',
                    'theme_version'        => '2.0.0.0',
                    'parent_id'            => null,
                    'parent_theme_path'    => null,
                    'is_featured'          => true,
                    'magento_version_from' => '2.0.0.0-dev1',
                    'magento_version_to'   => '*',
                    'theme_path'           => 'default/default',
                    'code'                 => 'default/default',
                    'preview_image'        => null,
                    'theme_directory'      => $designPath,
                    'type'                 => Mage_Core_Model_Theme::TYPE_PHYSICAL
                )
            )
        );
    }

    /**
     * Test is theme present in file system
     *
     * @magentoAppIsolation enabled
     * @covers Mage_Core_Model_Theme_Collection::hasTheme
     */
    public function testHasThemeInCollection()
    {
        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $themeModel->setData(array(
            'area'                 => 'space_area',
            'theme_title'          => 'Space theme',
            'theme_version'        => '2.0.0.0',
            'parent_id'            => null,
            'is_featured'          => false,
            'magento_version_from' => '2.0.0.0-dev1',
            'magento_version_to'   => '*',
            'theme_path'           => 'default/space',
            'preview_image'        => 'images/preview.png',
            'type'                 => Mage_Core_Model_Theme::TYPE_PHYSICAL
        ));

        $this->_model->addDefaultPattern('*');
        $this->assertFalse($this->_model->hasTheme($themeModel));
    }
}
