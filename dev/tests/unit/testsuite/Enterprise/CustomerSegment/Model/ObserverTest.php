<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Enterprise_CustomerSegment_Model_ObserverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Enterprise_CustomerSegment_Model_Observer
     */
    private $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $_segmentHelper;

    protected function setUp()
    {
        $this->_segmentHelper = $this->getMock(
            'Enterprise_CustomerSegment_Helper_Data', array('isEnabled', 'addSegmentFieldsToForm'), array(), '', false
        );
        $this->_model = new Enterprise_CustomerSegment_Model_Observer($this->_segmentHelper);
    }

    protected function tearDown()
    {
        $this->_model = null;
        $this->_segmentHelper = null;
    }

    public function testAddFieldsToTargetRuleForm()
    {
        $this->_segmentHelper->expects($this->any())->method('isEnabled')->will($this->returnValue(true));

        $formDependency = $this->getMock(
            'Mage_Backend_Block_Widget_Form_Element_Dependence', array(), array(), '', false
        );

        $layout = $this->getMock('Mage_Core_Model_Layout', array('createBlock'), array(), '', false);
        $layout
            ->expects($this->once())
            ->method('createBlock')
            ->with('Mage_Backend_Block_Widget_Form_Element_Dependence')
            ->will($this->returnValue($formDependency))
        ;

        $form = new Varien_Data_Form();
        $model = new Varien_Object();
        $block = new Varien_Object(array('layout' => $layout));

        $this->_segmentHelper
            ->expects($this->once())->method('addSegmentFieldsToForm')->with($form, $model, $formDependency);

        $this->_model->addFieldsToTargetRuleForm(new Varien_Event_Observer(array(
            'event' => new Varien_Object(array('form' => $form, 'model' => $model, 'block' => $block)),
        )));
    }

    public function testAddFieldsToTargetRuleFormDisabled()
    {
        $this->_segmentHelper->expects($this->any())->method('isEnabled')->will($this->returnValue(false));

        $layout = $this->getMock('Mage_Core_Model_Layout', array('createBlock'), array(), '', false);
        $layout->expects($this->never())->method('createBlock');

        $form = new Varien_Data_Form();
        $model = new Varien_Object();
        $block = new Varien_Object(array('layout' => $layout));

        $this->_segmentHelper->expects($this->never())->method('addSegmentFieldsToForm');

        $this->_model->addFieldsToTargetRuleForm(new Varien_Event_Observer(array(
            'event' => new Varien_Object(array('form' => $form, 'model' => $model, 'block' => $block)),
        )));
    }
}
