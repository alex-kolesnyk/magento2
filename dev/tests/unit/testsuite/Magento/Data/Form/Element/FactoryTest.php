<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Data
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Tests for \Magento\Data\Form\Element\Factory
 */
class Magento_Data_Form_Element_FactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Data\Form\Element\Factory
     */
    protected $_factory;

    public function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\ObjectManager\ObjectManager',
            array('create'), array(), '', false);
        $this->_factory = new \Magento\Data\Form\Element\Factory($this->_objectManagerMock);
    }

    /**
     * @param string $type
     * @dataProvider createPositiveDataProvider
     */
    public function testCreatePositive($type)
    {
        $className = 'Magento\Data\Form\Element\\' . ucfirst($type);
        $elementMock = $this->getMock($className, array(), array(), '', false);
        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($className), $this->equalTo(array()))
            ->will($this->returnValue($elementMock));
        $this->assertSame($elementMock, $this->_factory->create($type));
    }

    /**
     * @return array
     */
    public function createPositiveDataProvider()
    {
        return array(
            'button' => array('button'),
            'checkbox' => array('checkbox'),
            'checkboxes' => array('checkboxes'),
            'column' => array('column'),
            'date' => array('date'),
            'editablemultiselect' => array('editablemultiselect'),
            'editor' => array('editor'),
            'fieldset' => array('fieldset'),
            'file' => array('file'),
            'gallery' => array('gallery'),
            'hidden' => array('hidden'),
            'image' => array('image'),
            'imagefile' => array('imagefile'),
            'label' => array('label'),
            'link' => array('link'),
            'multiline' => array('multiline'),
            'multiselect' => array('multiselect'),
            'note' => array('note'),
            'obscure' => array('obscure'),
            'password' => array('password'),
            'radio' => array('radio'),
            'radios' => array('radios'),
            'reset' => array('reset'),
            'select' => array('select'),
            'submit' => array('submit'),
            'text' => array('text'),
            'textarea' => array('textarea'),
            'time' => array('time'),
        );
    }

    /**
     * @param string $type
     * @dataProvider createExceptionReflectionExceptionDataProvider
     * @expectedException ReflectionException
     */
    public function testCreateExceptionReflectionException($type)
    {
        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($type), $this->equalTo(array()))
            ->will($this->throwException(new ReflectionException()));
        $this->_factory->create($type);
    }

    /**
     * @return array
     */
    public function createExceptionReflectionExceptionDataProvider()
    {
        return array(
            'factory' => array('factory'),
            'collection' => array('collection'),
            'abstract' => array('abstract'),
        );
    }

    /**
     * @param string $type
     * @dataProvider createExceptionInvalidArgumentDataProvider
     * @expectedException InvalidArgumentException
     */
    public function testCreateExceptionInvalidArgument($type)
    {
        $elementMock = $this->getMock($type, array(), array(), '', false);
        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($type), $this->equalTo(array()))
            ->will($this->returnValue($elementMock));
        $this->_factory->create($type);
    }

    /**
     * @return array
     */
    public function createExceptionInvalidArgumentDataProvider()
    {
        return array(
            'Magento\Data\Form\Element\Factory' => array('Magento\Data\Form\Element\Factory'),
            'Magento\Data\Form\Element\Collection' => array('Magento\Data\Form\Element\Collection'),
        );
    }
}
