<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Magento_Catalog_Model_Product_Type_ConfigurableTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Type\Configurable
     */
    protected $_model;

    protected function setUp()
    {
        $eventManager = $this->getMock('Magento\Core\Model\Event\Manager', array(), array(), '', false);
        $coreDataMock = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);
        $fileStorageDbMock = $this->getMock('Magento\Core\Helper\File\Storage\Database', array(), array(), '', false);
        $filesystem = $this->getMockBuilder('Magento\Filesystem')->disableOriginalConstructor()->getMock();
        $coreRegistry = $this->getMock('Magento_Core_Model_Registry', array(), array(), '', false);
        $logger = $this->getMock('Magento_Core_Model_Logger', array(), array(), '', false);
        $this->_model = new Magento_Catalog_Model_Product_Type_Configurable(
            $eventManager,
            $coreDataMock,
            $fileStorageDbMock,
            $filesystem,
            $coreRegistry,
            $logger
        );
    }

    public function testHasWeightTrue()
    {
        $this->assertTrue($this->_model->hasWeight(), 'This product has not weight, but it should');
    }
}
