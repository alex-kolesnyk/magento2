<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Reminder
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Reminder\Block\Adminhtml\Reminder\Edit\Tab;

/**
 * Test class for \Magento\Reminder\Block\Adminhtml\Reminder\Edit\Tab\General
 * @magentoAppArea adminhtml
 */
class GeneralTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testPrepareForm()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\View\DesignInterface')
            ->setArea(\Magento\Core\Model\App\Area::AREA_ADMINHTML)
            ->setDefaultDesignTheme();
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Core\Model\Registry')
            ->register('current_reminder_rule', \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Reminder\Model\Rule'));

        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\View\LayoutInterface')
            ->createBlock('Magento\Reminder\Block\Adminhtml\Reminder\Edit\Tab\General');
        $prepareFormMethod = new \ReflectionMethod(
            'Magento\Reminder\Block\Adminhtml\Reminder\Edit\Tab\General', '_prepareForm');
        $prepareFormMethod->setAccessible(true);
        $prepareFormMethod->invoke($block);

        $form = $block->getForm();
        foreach (array('from_date', 'to_date') as $id) {
            $element = $form->getElement($id);
            $this->assertNotNull($element);
            $this->assertNotEmpty($element->getDateFormat());
        }
    }
}
