<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Magento_Catalog_Model_Resource_Eav_AttributeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Catalog_Model_Resource_Eav_Attribute
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model= Mage::getResourceModel('Magento_Catalog_Model_Resource_Eav_Attribute');
    }

    public function testCRUD()
    {
        $this->_model->setAttributeCode('test')
            ->setEntityTypeId(Mage::getSingleton('Magento_Eav_Model_Config')->getEntityType('catalog_product')->getId())
            ->setFrontendLabel('test');
        $crud = new Magento_Test_Entity($this->_model, array('frontend_label' => uniqid()));
        $crud->testCrud();
    }
}
