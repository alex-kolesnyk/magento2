<?php
/**
 * Test class for Mage_Core_Model_Dataservice_Path_Composite
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Core_Model_Dataservice_Path_CompositeTest extends PHPUnit_Framework_TestCase
{
    const RETURN_VALUE = 'RETURN_VALUE';

    const ITEM_ONE = 'ITEM_ONE';

    const ITEM_TWO = 'ITEM_TWO';

    const ITEM_THREE = 'ITEM_THREE';

    /** @var Mage_Core_Model_Dataservice_Path_Composite */
    protected $_composite;

    protected $_map;

    public function setup()
    {
        /** @var $objectManagerMock Magento_ObjectManager */
        $objectManagerMock = $this->getMockBuilder('Magento_ObjectManager')->disableOriginalConstructor()->getMock();
        $this->_map = array(
            array(self::ITEM_ONE, array(), (object)array('name' => self::ITEM_ONE)),
            array(self::ITEM_TWO, array(), (object)array('name' => self::ITEM_TWO)),
            array(self::ITEM_THREE, array(), (object)array('name' => self::ITEM_THREE))
        );
        $objectManagerMock->expects($this->any())->method('create')->will($this->returnValueMap($this->_map));
        $vector = array((self::ITEM_ONE)   => (self::ITEM_ONE),
                        (self::ITEM_TWO)   => (self::ITEM_TWO),
                        (self::ITEM_THREE) => (self::ITEM_THREE));
        $this->_composite
            = new Mage_Core_Model_Dataservice_Path_Composite($objectManagerMock, $vector);
    }

    /**
     * @dataProvider childrenProvider
     */
    public function testGetChildNode($elementName, $expectedResult)
    {
        $child = $this->_composite->getChild($elementName);

        $this->assertEquals($expectedResult, $child);
    }

    public function childrenProvider()
    {
        return array(
            array(self::ITEM_ONE, (object)array('name' => self::ITEM_ONE)),
            array(self::ITEM_TWO, (object)array('name' => self::ITEM_TWO)),
            array(self::ITEM_THREE, (object)array('name' => self::ITEM_THREE)),
        );
    }
}