<?php
/**
 * Parent class for Source tests that provides common functionality.
 *
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Webhook
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Webhook\Model\Source;

class Pkg extends \PHPUnit_Framework_TestCase
{
    /** Config values */
    const CONFIG_LABEL = 'blah';
    const CONFIG_STATUS = 'enabled';

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockConfig;
    
    /** @var \Magento\Core\Model\Config\Element */
    protected $_modelConfigElement;
    
    protected function setUp()
    {
        $label = self::CONFIG_LABEL;
        $status = self::CONFIG_STATUS;
        $this->_modelConfigElement = new \Magento\Core\Model\Config\Element(
            "<types><type><status>{$status}</status><label>{$label}</label></type></types>"
        );
        $this->_mockConfig = $this->getMockBuilder('Magento\Core\Model\Config')
            ->disableOriginalConstructor()->getMock();
        $this->_mockConfig->expects($this->any())
            ->method('getNode')
            ->will($this->returnValue($this->_modelConfigElement));
    }

    /**
     * Asserts that the elements array contains the expected label and value.
     *
     * @param $elements
     */
    protected function _assertElements($elements)
    {
        $this->assertSame(self::CONFIG_LABEL, $elements[0]['label']);
        $this->assertSame('type', $elements[0]['value']);
    }
}
