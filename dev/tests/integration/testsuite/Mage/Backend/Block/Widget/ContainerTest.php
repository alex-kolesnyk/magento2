<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Backend_Block_Widget_ContainerTest extends Mage_Backend_Area_TestCase
{
    public function testPseudoConstruct()
    {
        /** @var $block Mage_Backend_Block_Widget_Container */
        $block = Mage::app()->getLayout()->createBlock('Mage_Backend_Block_Widget_Container', '', array('data' => array(
            Mage_Backend_Block_Widget_Container::PARAM_CONTROLLER => 'one',
            Mage_Backend_Block_Widget_Container::PARAM_HEADER_TEXT => 'two',
        )));
        $this->assertStringEndsWith('one', $block->getHeaderCssClass());
        $this->assertContains('two', $block->getHeaderText());
    }
}
