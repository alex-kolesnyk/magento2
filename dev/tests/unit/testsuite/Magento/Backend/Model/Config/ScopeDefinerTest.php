<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Backend
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Magento_Backend_Model_Config_ScopeDefinerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Backend_Model_Config_ScopeDefiner
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    protected function setUp()
    {
        $this->_requestMock = $this->getMock('Magento_Core_Controller_Request_Http', array(), array(), '', false);
        $this->_model = new Magento_Backend_Model_Config_ScopeDefiner($this->_requestMock);
    }

    public function testGetScopeReturnsDefaultScopeIfNoScopeDataIsSpecified()
    {
        $this->assertEquals(Magento_Backend_Model_Config_ScopeDefiner::SCOPE_DEFAULT, $this->_model->getScope());
    }

    public function testGetScopeReturnsStoreScopeIfStoreIsSpecified()
    {
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap(array(
            array('website', null, 'someWebsite'),
            array('store', null, 'someStore')
        )));
        $this->assertEquals(Magento_Backend_Model_Config_ScopeDefiner::SCOPE_STORE, $this->_model->getScope());
    }

    public function testGetScopeReturnsWebsiteScopeIfWebsiteIsSpecified()
    {
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap(array(
            array('website', null, 'someWebsite'),
            array('store', null, null)
        )));
        $this->assertEquals(Magento_Backend_Model_Config_ScopeDefiner::SCOPE_WEBSITE, $this->_model->getScope());
    }
}
