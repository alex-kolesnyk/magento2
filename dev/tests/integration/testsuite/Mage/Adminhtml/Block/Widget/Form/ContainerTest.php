<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Adminhtml_Block_Widget_Form_ContainerTest extends Mage_Backend_Area_TestCase
{
    public function testGetFormHtml()
    {
        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getModel('Mage_Core_Model_Layout');
        // Create block with blocking _prepateLayout(), which is used by block to instantly add 'form' child
        /** @var $block Mage_Adminhtml_Block_Widget_Form_Container */
        $block = $this->getMock('Mage_Adminhtml_Block_Widget_Form_Container', array('_prepareLayout'),
            array(Mage::getModel('Mage_Core_Block_Template_Context'))
        );

        $layout->addBlock($block, 'block');
        $form = $layout->addBlock('Mage_Core_Block_Text', 'form', 'block');

        $expectedHtml = '<b>html</b>';
        $this->assertNotEquals($expectedHtml, $block->getFormHtml());
        $form->setText($expectedHtml);
        $this->assertEquals($expectedHtml, $block->getFormHtml());
    }
}
