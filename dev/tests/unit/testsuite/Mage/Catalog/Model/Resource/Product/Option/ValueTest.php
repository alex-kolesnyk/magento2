<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_ImportExport
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Catalog_Model_Resource_Product_Option_ValueTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Stub_UnitTest_Mage_Catalog_Model_Resource_Product_Option_Value
     */
    protected $_object;

    /**
     * Store old display_errors ini option value here
     *
     * @var int
     */
    protected $_oldDisplayErrors;

    /**
     * Store old error_reporting ini option value here
     *
     * @var int
     */
    protected $_oldErrorLevel;

    /**
     * Store old isDeveloperMode value here
     *
     * @var boolean
     */
    protected $_oldIsDeveloperMode;

    /**
     * Option value title data
     *
     * @var array
     */
    public static $valueTitleData = array(
        'id'       => 2,
        'store_id' => Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID,
        'scope'    => array('title' => 1)
    );

    protected function setUp()
    {
        parent::setUp();

        $this->_object = new Stub_UnitTest_Mage_Catalog_Model_Resource_Product_Option_Value();

        $this->_oldDisplayErrors  = ini_get('display_errors');
        $this->_oldErrorLevel = error_reporting();
        $this->_oldIsDeveloperMode = Mage::getIsDeveloperMode();
    }

    protected function tearDown()
    {
        ini_set('display_errors', $this->_oldDisplayErrors);
        error_reporting($this->_oldErrorLevel);
        Mage::setIsDeveloperMode($this->_oldIsDeveloperMode);

        unset($this->_object);

        parent::tearDown();
    }

    /**
     * Test that there is no notice in _saveValueTitles()
     *
     * @covers Mage_Catalog_Model_Resource_Product_Option_Value::_saveValueTitles
     */
    public function testSaveValueTitles()
    {
        $object = new Stub_UnitTest_Mage_Catalog_Model_Resource_Product_Option_Value_Mage_Core_Model_Stub(
            $this->getMock('Mage_Core_Model_Context', array(), array(), '', false),
            null,
            null,
            self::$valueTitleData
        );

        // we have to set strict error reporting mode and enable mage developer mode to convert notice to exception
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_errors', 1);
        Mage::setIsDeveloperMode(true);

        $this->_object->saveValueTitles($object);
    }
}

class Stub_UnitTest_Mage_Catalog_Model_Resource_Product_Option_Value
    extends Mage_Catalog_Model_Resource_Product_Option_Value
{
    /**
     * Stub parent constructor
     */
    public function __construct()
    {
        $this->_connections = array(
            'read' => new Stub_UnitTest_Mage_Catalog_Model_Resource_Product_Option_Value_Varien_Db_Adapter_Pdo_Mysql(),
            'write' => new Stub_UnitTest_Mage_Catalog_Model_Resource_Product_Option_Value_Varien_Db_Adapter_Pdo_Mysql(),
        );
    }

    /**
     * Save option value price data
     *
     * @param Mage_Core_Model_Abstract $object
     */
    public function saveValueTitles(Mage_Core_Model_Abstract $object)
    {
        $this->_saveValueTitles($object);
    }

    /**
     * We should stub to not use db
     *
     * @param string $tableName
     * @return string
     */
    public function getTable($tableName)
    {
        return $tableName;
    }
}

/*
 * Extend Varien_Db_Adapter_Pdo_Mysql and stub needed methods
 */
class Stub_UnitTest_Mage_Catalog_Model_Resource_Product_Option_Value_Varien_Db_Adapter_Pdo_Mysql
    extends Varien_Db_Adapter_Pdo_Mysql
{
    /**
     * Disable parent constructor
     */
    public function __construct()
    {
    }

    /**
     * Stub delete method and add needed asserts
     *
     * @param  string $table
     * @param  array|string $where
     * @return int
     */
    public function delete($table, $where = '')
    {
        PHPUnit_Framework_TestCase::assertEquals('catalog_product_option_type_title', $table);
        PHPUnit_Framework_TestCase::assertInternalType('array', $where);
        PHPUnit_Framework_TestCase::assertEquals(
            Mage_Catalog_Model_Resource_Product_Option_ValueTest::$valueTitleData['id'],
            $where['option_type_id = ?']
        );
        PHPUnit_Framework_TestCase::assertEquals(
            Mage_Catalog_Model_Resource_Product_Option_ValueTest::$valueTitleData['store_id'],
            $where['store_id = ?']
        );

        return 0;
    }
}

/*
 * Because Mage_Core_Model_Abstract is abstract - we can't instantiate it directly
 */
class Stub_UnitTest_Mage_Catalog_Model_Resource_Product_Option_Value_Mage_Core_Model_Stub
    extends Mage_Core_Model_Abstract
{
}
