<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Core\Model\TemplateEngine\Plugin;

class DebugHintsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DebugHints
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_coreData;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\ObjectManager');
        $this->_storeConfig = $this->getMock('Magento\Core\Model\Store\Config', array(), array(), '', false);
        $this->_coreData = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);
        $this->_model = new DebugHints($this->_objectManager, $this->_storeConfig, $this->_coreData);
    }

    /**
     * @param bool $showBlockHints
     * @dataProvider afterCreateActiveDataProvider
     */
    public function testAfterCreateActive($showBlockHints)
    {
        $this->_coreData->expects($this->once())->method('isDevAllowed')->will($this->returnValue(true));
        $this->_setupConfigFixture(true, $showBlockHints);
        $engine = $this->getMock('Magento\Core\Model\TemplateEngine\EngineInterface');
        $engineDecorated = $this->getMock('Magento\Core\Model\TemplateEngine\EngineInterface');
        $this->_objectManager
            ->expects($this->once())
            ->method('create')
            ->with(
                'Magento\Core\Model\TemplateEngine\Decorator\DebugHints',
                $this->identicalTo(array('subject' => $engine, 'showBlockHints' => $showBlockHints))
            )
            ->will($this->returnValue($engineDecorated))
        ;
        $this->assertEquals($engineDecorated, $this->_model->afterCreate($engine));
    }

    public function afterCreateActiveDataProvider()
    {
        return array(
            'block hints disabled'  => array(false),
            'block hints enabled'   => array(true),
        );
    }

    /**
     * @param bool $isDevAllowed
     * @param bool $showTemplateHints
     * @dataProvider afterCreateInactiveDataProvider
     */
    public function testAfterCreateInactive($isDevAllowed, $showTemplateHints)
    {
        $this->_coreData->expects($this->any())->method('isDevAllowed')->will($this->returnValue($isDevAllowed));
        $this->_setupConfigFixture($showTemplateHints, true);
        $this->_objectManager->expects($this->never())->method('create');
        $engine = $this->getMock('Magento\Core\Model\TemplateEngine\EngineInterface');
        $this->assertSame($engine, $this->_model->afterCreate($engine));
    }

    public function afterCreateInactiveDataProvider()
    {
        return array(
            'dev disabled, template hints disabled' => array(false, false),
            'dev disabled, template hints enabled'  => array(false, true),
            'dev enabled, template hints disabled'  => array(true, false),
        );
    }

    /**
     * Setup fixture values for store config
     *
     * @param bool $showTemplateHints
     * @param bool $showBlockHints
     */
    protected function _setupConfigFixture($showTemplateHints, $showBlockHints)
    {
        $this->_storeConfig->expects($this->atLeastOnce())->method('getConfig')->will($this->returnValueMap(array(
            array(DebugHints::XML_PATH_DEBUG_TEMPLATE_HINTS, null, $showTemplateHints),
            array(DebugHints::XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS, null, $showBlockHints),
        )));
    }
}
