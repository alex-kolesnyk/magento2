<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_GiftCardAccount
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\GiftCardAccount\Block\Adminhtml\Giftcardaccount\Edit\Tab;

/**
 * @magentoAppArea adminhtml
 */
class SendTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\GiftCardAccount\Block\Adminhtml\Giftcardaccount\Edit\Tab\Send */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();

        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\GiftCardAccount\Model\Giftcardaccount');
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Core\Model\Registry')->register('current_giftcardaccount', $model);

        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\Layout');

        $this->_block = $layout
            ->createBlock('Magento\GiftCardAccount\Block\Adminhtml\Giftcardaccount\Edit\Tab\Send');
    }

    protected function tearDown()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Core\Model\Registry')->unregister('current_giftcardaccount');
        parent::tearDown();
    }

    /**
     * Test Prepare Form in Single Store mode
     *
     * @magentoConfigFixture current_store general/single_store_mode/enabled 1
     */
    public function testPrepareFormSingleStore()
    {
        $this->_block->initForm();
        $form = $this->_block->getForm();
        $this->assertEquals('base_fieldset', $form->getElement('base_fieldset')->getId());
        $this->assertNull($form->getElement('store_id'));
    }

    /**
     * Test Prepare Form in Multiple Store mode
     *
     * @magentoConfigFixture current_store general/single_store_mode/enabled 0
     */
    public function testPrepareFormMultipleStore()
    {
        $this->_block->initForm();
        $form = $this->_block->getForm();
        $this->assertEquals('base_fieldset', $form->getElement('base_fieldset')->getId());

        $element = $form->getElement('store_id');
        $this->assertNotNull($element);
        $this->assertInstanceOf('Magento\Data\Form\Element\Select', $element);
        $this->assertEquals('store_id', $element->getId());
    }
}
