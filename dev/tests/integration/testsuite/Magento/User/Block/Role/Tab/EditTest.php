<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_User
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * @magentoAppArea adminhtml
 */
class Magento_User_Block_Role_Tab_EditTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_User_Block_Role_Tab_Edit
     */
    protected $_block;

    protected function setUp()
    {
        $roleAdmin = Magento_TestFramework_Helper_Bootstrap::getObjectManager()
            ->create('Magento_User_Model_Role');
        $roleAdmin->load(Magento_TestFramework_Bootstrap::ADMIN_ROLE_NAME, 'role_name');
        Magento_TestFramework_Helper_Bootstrap::getObjectManager()->get('Magento_Core_Controller_Request_Http')
            ->setParam('rid', $roleAdmin->getId());

        $this->_block = Magento_TestFramework_Helper_Bootstrap::getObjectManager()
            ->create('Magento_User_Block_Role_Tab_Edit');
    }

    public function testConstructor()
    {
        $this->assertNotEmpty($this->_block->getSelectedResources());
        $this->assertContains(
            'Magento_Adminhtml::all',
            $this->_block->getSelectedResources()
        );
    }

    public function testGetTree()
    {
        $encodedTree = $this->_block->getTree();
        $this->assertNotEmpty($encodedTree);
    }
}
