<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Catalog\Model\Attribute\Config;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Attribute\Config\Reader
     */
    protected $_model;

    /**
     * @var \Magento\Config\FileResolverInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileResolverMock;

    /**
     * @var \Magento\Catalog\Model\Attribute\Config\Converter|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_converter;

    /**
     * @var \Magento\Catalog\Model\Attribute\Config\SchemaLocator
     */
    protected $_schemaLocator;

    /**
     * @var \Magento\Config\ValidationStateInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_validationState;

    protected function setUp()
    {
        $this->_fileResolverMock = $this->getMock('Magento\Config\FileResolverInterface');
        $this->_fileResolverMock
            ->expects($this->once())
            ->method('get')
            ->with('catalog_attributes.xml', 'scope')
            ->will($this->returnValue(array(
                __DIR__ . '/_files/attributes_config_one.xml',
                __DIR__ . '/_files/attributes_config_two.xml',
            )))
        ;

        $this->_converter = $this->getMock('Magento\Catalog\Model\Attribute\Config\Converter', array('convert'));

        $moduleReader = $this->getMock(
            'Magento\Core\Model\Config\Modules\Reader', array('getModuleDir'), array(), '', false
        );
        $moduleReader
            ->expects($this->once())
            ->method('getModuleDir')->with('etc', 'Magento_Catalog')
            ->will($this->returnValue('stub'))
        ;
        $this->_schemaLocator = new \Magento\Catalog\Model\Attribute\Config\SchemaLocator($moduleReader);

        $this->_validationState = $this->getMock('Magento\Config\ValidationStateInterface');
        $this->_validationState->expects($this->once())->method('isValidated')->will($this->returnValue(false));

        $this->_model = new \Magento\Catalog\Model\Attribute\Config\Reader(
            $this->_fileResolverMock,
            $this->_converter,
            $this->_schemaLocator,
            $this->_validationState
        );
    }

    public function testRead()
    {
        $expectedResult = new \stdClass();
        $constraint = function (DOMDOcument $actual) {
            try {
                $expected = __DIR__ . '/_files/attributes_config_merged.xml';
                PHPUnit_Framework_Assert::assertXmlStringEqualsXmlFile($expected, $actual->saveXML());
                return true;
            } catch (PHPUnit_Framework_AssertionFailedError $e) {
                return false;
            }
        };
        $this->_converter
            ->expects($this->once())
            ->method('convert')
            ->with($this->callback($constraint))
            ->will($this->returnValue($expectedResult))
        ;
        $this->assertSame($expectedResult, $this->_model->read('scope'));
    }

}
