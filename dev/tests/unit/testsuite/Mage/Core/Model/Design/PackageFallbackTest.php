<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test that Design Package delegates fallback resolution to a Fallback model
 */
class Mage_Core_Model_Design_PackageFallbackTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Design_Package|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var Mage_Core_Model_Design_Fallback|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fallback;

    protected function setUp()
    {
        $this->_model = $this->getMock('Mage_Core_Model_Design_Package', array('_updateParamDefaults', '_getFallback'),
            array(), '', false
        );
        $this->_fallback = $this->getMock('Mage_Core_Model_Design_Package_Fallback',
            array('getFile', 'getLocaleFile', 'getSkinFile')
        );
    }

    public function testGetFilename()
    {
        $params = array(
            'area' => 'some_area',
            'package' => 'some_package',
            'theme' => 'some_theme',
        );
        $file = 'Some_Module::some_file.ext';
        $expectedParams = $params + array('module' => 'Some_Module');
        $expected = 'path/to/some_file.ext';

        $this->_model->expects($this->once())
            ->method('_getFallback')
            ->with($expectedParams)
            ->will($this->returnValue($this->_fallback));
        $this->_fallback->expects($this->once())
            ->method('getFile')
            ->with('some_file.ext', 'Some_Module')
            ->will($this->returnValue($expected));

        $actual = $this->_model->getFilename($file, $params);
        $this->assertEquals($expected, $actual);
    }

    public function testGetLocaleFileName()
    {
        $params = array(
            'area' => 'some_area',
            'package' => 'some_package',
            'theme' => 'some_theme',
            'locale' => 'some_locale'
        );
        $file = 'some_file.ext';
        $expected = 'path/to/some_file.ext';

        $this->_model->expects($this->once())
            ->method('_getFallback')
            ->with($params)
            ->will($this->returnValue($this->_fallback));
        $this->_fallback->expects($this->once())
            ->method('getLocaleFile')
            ->with('some_file.ext')
            ->will($this->returnValue($expected));

        $actual = $this->_model->getLocaleFileName($file, $params);
        $this->assertEquals($expected, $actual);
    }

    public function testGetSkinFile()
    {
        $params = array(
            'area' => 'some_area',
            'package' => 'some_package',
            'theme' => 'some_theme',
            'skin' => 'some_skin',
            'locale' => 'some_locale'
        );
        $file = 'Some_Module::some_file.ext';
        $expectedParams = $params + array('module' => 'Some_Module');
        $expected = 'path/to/some_file.ext';

        $this->_model->expects($this->once())
            ->method('_getFallback')
            ->with($expectedParams)
            ->will($this->returnValue($this->_fallback));
        $this->_fallback->expects($this->once())
            ->method('getSkinFile')
            ->with('some_file.ext', 'Some_Module')
            ->will($this->returnValue($expected));

        $actual = $this->_model->getSkinFile($file, $params);
        $this->assertEquals($expected, $actual);
    }
}
