<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Simplexml;

class ElementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider xmlDataProvider
     */
    public function testUnsetSelf($xmlData)
    {
        /** @var $xml \Magento\Simplexml\Element */
        $xml = simplexml_load_file($xmlData[0], $xmlData[1]);
        $this->assertTrue(isset($xml->node3->node4));
        $xml->node3->unsetSelf();
        $this->assertFalse(isset($xml->node3->node4));
        $this->assertFalse(isset($xml->node3));
        $this->assertTrue(isset($xml->node1));
    }

    /**
     * @dataProvider xmlDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Root node could not be unset.
     */
    public function testGetParent($xmlData)
    {
        /** @var $xml \Magento\Simplexml\Element */
        $xml = simplexml_load_file($xmlData[0], $xmlData[1]);
        $this->assertTrue($xml->getName() == 'root');
        $xml->unsetSelf();
    }

    /**
     * Data Provider for testUnsetSelf and testUnsetSelfException
     */
    public static function xmlDataProvider()
    {
        return array(
            array(array(__DIR__ . '/_files/data.xml', 'Magento\Simplexml\Element'))
        );
    }

    public function testAsNiceXmlMixedData()
    {
        $dataFile = file_get_contents(__DIR__ . '/_files/mixed_data.xml');
        /** @var \Magento\Simplexml\Element $xml  */
        $xml = simplexml_load_string($dataFile, 'Magento\Simplexml\Element');

        $expected = <<<XML
<root>
   <node_1 id="1">Value 1
      <node_1_1>Value 1.1
         <node_1_1_1>Value 1.1.1</node_1_1_1>
      </node_1_1>
   </node_1>
   <node_2>
      <node_2_1>Value 2.1</node_2_1>
   </node_2>
</root>

XML;
        $this->assertEquals($expected, $xml->asNiceXml());
    }

    public function testAppendChild()
    {
        /** @var \Magento\Simplexml\Element $baseXml */
        $baseXml = simplexml_load_string('<root/>', 'Magento\Simplexml\Element');
        /** @var \Magento\Simplexml\Element $appendXml */
        $appendXml = simplexml_load_string(
            '<node_a attr="abc"><node_b>text</node_b></node_a>',
            'Magento\Simplexml\Element'
        );
        $baseXml->appendChild($appendXml);

        $expectedXml = '<root><node_a attr="abc"><node_b>text</node_b></node_a></root>';
        $this->assertXmlStringEqualsXmlString($expectedXml, $baseXml->asNiceXml());
    }

    public function testSetNode()
    {
        $path = '/node1/node2';
        $value = 'value';
        /** @var \Magento\Simplexml\Element $xml */
        $xml = simplexml_load_string('<root/>', 'Magento\Simplexml\Element');
        $this->assertEmpty($xml->xpath('/root/node1/node2'));
        $xml->setNode($path, $value);
        $this->assertNotEmpty($xml->xpath('/root/node1/node2'));
        $this->assertEquals($value, (string)$xml->xpath('/root/node1/node2')[0]);
    }
}
