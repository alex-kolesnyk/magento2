<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Core_Model_Module_Declaration_Converter_DomTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Module_Declaration_Converter_Dom
     */
    protected $_converter;

    protected function setUp()
    {
        $this->_converter = new Mage_Core_Model_Module_Declaration_Converter_Dom();
    }

    public function testConvertWithValidDom()
    {
        $xmlFilePath = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/_files/valid_module.xml');
        $dom = new DOMDocument();
        $dom->loadXML(file_get_contents($xmlFilePath));
        $expectedResult = include __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/_files/converted_valid_module.php');
        $this->assertEquals($expectedResult, $this->_converter->convert($dom));
    }

    /**
     * @param string $xmlString
     * @dataProvider testConvertWithInvalidDomDataProvider
     * @expectedException Exception
     */
    public function testConvertWithInvalidDom($xmlString)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xmlString);
        $this->_converter->convert($dom);
    }

    public function testConvertWithInvalidDomDataProvider()
    {
        return array(
            'Module node without "name" attribute' => array(
                '<?xml version="1.0"?><config><module /></config>'
            ),
            'Module node without "version" attribute' => array(
                '<?xml version="1.0"?><config><module name="Module_One" /></config>'
            ),
            'Module node without "active" attribute' => array(
                '<?xml version="1.0"?><config><module name="Module_One" version="1.0.0.0" /></config>'
            ),
            'Dependency module node without "name" attribute' => array(
                '<?xml version="1.0"?><config><module name="Module_One" version="1.0.0.0" active="true">'
                    . '<depends><module/></depends></module></config>'
            ),
            'Dependency extension node without "name" attribute' => array(
                '<?xml version="1.0"?><config><module name="Module_One" version="1.0.0.0" active="true">'
                . '<depends><extension/></depends></module></config>'
            ),
            'Empty choice node' => array(
                '<?xml version="1.0"?><config><module name="Module_One" version="1.0.0.0" active="true">'
                . '<depends><choice/></depends></module></config>'
            ),
        );
    }
}
