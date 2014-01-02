<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Catalog\Model\System\Config\Source;

class InputtypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_helper;

    /**
     * @var \Magento\Catalog\Model\System\Config\Source\Inputtype
     */
    protected $_model;

    protected function setUp()
    {
        $this->_helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $this->_helper->getObject('\Magento\Catalog\Model\System\Config\Source\Inputtype');
    }

    public function testToOptionArrayIsArray()
    {
        $this->assertInternalType('array', $this->_model->toOptionArray());
    }

    public function testToOptionArrayValid()
    {
        $expects = array(
            array('value' => 'multiselect', 'label' => 'Multiple Select'),
            array('value' => 'select', 'label' => 'Dropdown')

        );
        $this->assertEquals($expects, $this->_model->toOptionArray());
    }
}
