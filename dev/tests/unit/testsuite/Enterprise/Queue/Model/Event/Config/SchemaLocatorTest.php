<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Enterprise_Queue_Model_Event_Config_SchemaLocatorTest extends PHPUnit_Framework_TestCase
{
    /**
    * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleReaderMock;

    /**
    * @var Enterprise_Queue_Model_Event_Config_SchemaLocator
     */
    protected $_model;

    protected function setUp()
    {
        /** @todo Implement test logic here */
        
        $this->_moduleReaderMock = $this->getMock('Mage_Core_Model_Config_Modules_Reader', array(), array(), '', false);
        
        $this->_model = new Enterprise_Queue_Model_Event_Config_SchemaLocator($this->_moduleReaderMock);
    }

    public function testGetSchema()
    {
        /** @todo Implement test logic here */

        $this->_model->getSchema();
    }

    public function testGetPerFileSchema()
    {
        /** @todo Implement test logic here */

        $this->_model->getPerFileSchema();
    }
}