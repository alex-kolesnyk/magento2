<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_DesignEditor
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Magento_DesignEditor_Block_Adminhtml_Editor_Tools_Files_TreeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Backend_Model_Url|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilder;

    /**
     * @var Magento_Theme_Helper_Storage|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperStorage;

    /**
     * @var Magento_Theme_Block_Adminhtml_Wysiwyg_Files_Tree|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesTree;

    protected function setUp()
    {
        $this->_helperStorage = $this->getMock('Magento_Theme_Helper_Storage', array(), array(), '', false);
        $this->_urlBuilder = $this->getMock('Magento_Backend_Model_Url', array(), array(), '', false);

        $objectManagerHelper = new Magento_TestFramework_Helper_ObjectManager($this);
        $constructArguments =  $objectManagerHelper->getConstructArguments(
            'Magento_DesignEditor_Block_Adminhtml_Editor_Tools_Files_Content',
            array('urlBuilder'    => $this->_urlBuilder)
        );
        $this->_filesTree = $this->getMock(
            'Magento_DesignEditor_Block_Adminhtml_Editor_Tools_Files_Tree', array('helper'), $constructArguments
        );

        $this->_filesTree->expects($this->any())
            ->method('helper')
            ->with('Magento_Theme_Helper_Storage')
            ->will($this->returnValue($this->_helperStorage));
    }

    public function testGetTreeLoaderUrl()
    {
        $requestParams = array(
            Magento_Theme_Helper_Storage::PARAM_THEME_ID     => 1,
            Magento_Theme_Helper_Storage::PARAM_CONTENT_TYPE => Magento_Theme_Model_Wysiwyg_Storage::TYPE_IMAGE,
            Magento_Theme_Helper_Storage::PARAM_NODE         => 'root'
        );
        $expectedUrl = 'some_url';

        $this->_helperStorage->expects($this->once())
            ->method('getRequestParams')
            ->will($this->returnValue($requestParams));

        $this->_urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/*/treeJson', $requestParams)
            ->will($this->returnValue($expectedUrl));

        $this->assertEquals($expectedUrl, $this->_filesTree->getTreeLoaderUrl());
    }
}
