<?php
/**
 * Find "widget.xml" files and validate them
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Test\Integrity\Magento\Widget;

class WidgetConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $configFile
     *
     * @dataProvider xmlDataProvider
     */
    public function testXml($configFile)
    {
        $schema = \Magento\TestFramework\Utility\Files::init()->getPathToSource()
            . '/app/code/Magento/Widget/etc/widget.xsd';
        $this->_validateFileExpectSuccess($configFile, $schema);
    }

    /**
     * Find all widget.xml files in Magento
     *
     * @return array of widget.xml file paths
     */
    public function xmlDataProvider()
    {
        $utilityFiles = \Magento\TestFramework\Utility\Files::init();
        return array_merge(
            $utilityFiles->getConfigFiles('widget.xml'),
            $utilityFiles->getLayoutConfigFiles('widget.xml')
        );
    }

    public function testSchemaUsingValidXml()
    {
        $xmlFile = __DIR__ . '/_files/widget.xml';
        $schema = \Magento\TestFramework\Utility\Files::init()->getPathToSource()
            . '/app/code/Magento/Widget/etc/widget.xsd';
        $this->_validateFileExpectSuccess($xmlFile, $schema);
    }

    public function testSchemaUsingInvalidXml()
    {
        $xmlFile = __DIR__ . '/_files/invalid_widget.xml';
        $schema = \Magento\TestFramework\Utility\Files::init()->getPathToSource()
            . '/app/code/Magento/Widget/etc/widget.xsd';
        $this->_validateFileExpectFailure($xmlFile, $schema);
    }

    public function testFileSchemaUsingXml()
    {
        $xmlFile = __DIR__ . '/_files/widget_file.xml';
        $schema = \Magento\TestFramework\Utility\Files::init()->getPathToSource()
            . '/app/code/Magento/Widget/etc/widget_file.xsd';
        $this->_validateFileExpectSuccess($xmlFile, $schema);
    }

    public function testFileSchemaUsingInvalidXml()
    {
        $xmlFile = __DIR__ . '/_files/invalid_widget.xml';
        $schema = \Magento\TestFramework\Utility\Files::init()->getPathToSource()
            . '/app/code/Magento/Widget/etc/widget_file.xsd';
        $this->_validateFileExpectFailure($xmlFile, $schema);
    }

    /**
     * Run schema validation against an xml file with a provided schema.
     *
     * This helper expects the validation to pass and will fail a test if any errors are found.
     *
     * @param $xmlFile string a known good xml file.
     * @param $schemaFile string schema that should find no errors in the known good xml file.
     */
    protected function _validateFileExpectFailure($xmlFile, $schemaFile)
    {
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $errors = \Magento\Config\Dom::validateDomDocument($dom, $schemaFile);
        if (!$errors) {
            $this->fail('There is a problem with the schema.  A known bad XML file passed validation');
        }
    }

    /**
     * Run schema validation against a known bad xml file with a provided schema.
     *
     * This helper expects the validation to fail and will fail a test if no errors are found.
     *
     * @param $xmlFile string a known bad xml file.
     * @param $schemaFile string schema that should find errors in the known bad xml file.
     */
    protected function _validateFileExpectSuccess($xmlFile, $schemaFile)
    {
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $errors = \Magento\Config\Dom::validateDomDocument($dom, $schemaFile);
        if ($errors) {
            $this->fail('There is a problem with the schema.  A known good XML file failed validation: '
                . PHP_EOL . implode(PHP_EOL . PHP_EOL, $errors));
        }
    }
}
