<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Adminhtml
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for Mage_Adminhtml_Block_System_Design_Edit_Tab_General.
 *
 * @group module:Mage_Adminhtml
 */
class Mage_Adminhtml_Block_System_Design_Edit_Tab_GeneralTest extends PHPUnit_Framework_TestCase
{
    public function testPrepareForm()
    {
        Mage::register('design', Mage::getModel('Mage_Core_Model_Design'));
        $layout = new Mage_Core_Model_Layout;
        $block = $layout->addBlock('Mage_Adminhtml_Block_System_Design_Edit_Tab_General');
        $prepareFormMethod = new ReflectionMethod(
            'Mage_Adminhtml_Block_System_Design_Edit_Tab_General', '_prepareForm');
        $prepareFormMethod->setAccessible(true);
        $prepareFormMethod->invoke($block);

        $form = $block->getForm();
        foreach (array('date_from', 'date_to') as $id) {
            $element = $form->getElement($id);
            $this->assertNotNull($element);
            $this->assertNotEmpty($element->getDateFormat());
        }
    }
}
