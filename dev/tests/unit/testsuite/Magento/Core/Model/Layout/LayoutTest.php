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

class Magento_Core_Model_LayoutTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Core_Model_Layout
     */
    protected $_layout;

    public function setUp()
    {
        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $this->_layout = $objectManagerHelper->getObject('Magento_Core_Model_Layout');
    }

    /**
     * @dataProvider translateArgumentDataProvider
     * @param string $argument
     */
    public function testTranslateArgument($argument)
    {
        $reflectionObject = new ReflectionObject($this->_layout);
        $reflectionMethod = $reflectionObject->getMethod('_translateArgument');
        $reflectionMethod->setAccessible(true);
        $result = $reflectionMethod->invoke($this->_layout, new Magento_Simplexml_Element($argument));
        $this->assertInternalType('string', $result);
    }

    /**
     * @see self::testTranslateArgument();
     * @return array
     */
    public function translateArgumentDataProvider()
    {
        return array(
            array('<argument name="argumentName">phrase</argument>'),
            array('<argument name="argumentName" translate="true">phrase</argument>'),
        );
    }

    /**
     * @dataProvider translateArgumentsDataProvider
     * @param string $method
     * @param string $layoutElement
     */
    public function testTranslateArguments($method, $layoutElement)
    {
        $reflectionObject = new ReflectionObject($this->_layout);
        $reflectionMethod = $reflectionObject->getMethod($method);
        $reflectionMethod->setAccessible(true);
        $result = $reflectionMethod->invoke($this->_layout, new Magento_Core_Model_Layout_Element($layoutElement));
        $argument = $method == '_readArguments' ? $result['argumentName']['value'] : $result['argumentName'];

        $this->assertInternalType('string', $argument);
    }

    /**
     * @see self::testsFillArgumentsArray();
     * @return array
     */
    public function translateArgumentsDataProvider()
    {
        $result = array();
        $methods = array('_extractArgs', '_fillArgumentsArray', '_readArguments');

        $inputData = array(
            '<arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                <argument xsi:type="string" name="argumentName">phrase</argument>
            </arguments>',
            '<arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                <argument xsi:type="string" name="argumentName" translate="true"><value>phrase</value></argument>
            </arguments>',
            '<arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                <argument xsi:type="string" name="argumentName" translate="true"><value>phrase</value></argument>
            </arguments>'
        );

        foreach ($methods as $method) {
            $result[] = array($method, $inputData[0]);
            $result[] = array($method, $inputData[1]);
            $result[] = array($method, $inputData[2]);
        }
        return $result;
    }

}
