<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\ImportExport\Model\Import\Entity;

class CustomerCompositeTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Attributes used in test assertions
     */
    const ATTRIBUTE_CODE_FIRST_NAME = 'firstname';
    const ATTRIBUTE_CODE_LAST_NAME  = 'lastname';
    /**#@-*/

    /**#@+
     * Source *.csv file names for different behaviors
     */
    const UPDATE_FILE_NAME = 'customer_composite_update.csv';
    const DELETE_FILE_NAME    = 'customer_composite_delete.csv';
    /**#@-*/

    /**
     * Object Manager instance
     *
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    /**
     * Composite customer entity adapter instance
     *
     * @var \Magento\ImportExport\Model\Import\Entity\CustomerComposite
     */
    protected $_entityAdapter;

    /**
     * Additional customer attributes for assertion
     *
     * @var array
     */
    protected $_customerAttributes = array(
        self::ATTRIBUTE_CODE_FIRST_NAME,
        self::ATTRIBUTE_CODE_LAST_NAME,
    );

    /**
     * Customers and addresses before import, address ID is postcode
     *
     * @var array
     */
    protected $_beforeImport = array(
        'betsyparker@example.com' => array(
            'addresses' => array('19107', '72701'),
            'data' => array(
                self::ATTRIBUTE_CODE_FIRST_NAME => 'Betsy',
                self::ATTRIBUTE_CODE_LAST_NAME  => 'Parker',
            ),
        ),
    );

    /**
     * Customers and addresses after import, address ID is postcode
     *
     * @var array
     */
    protected $_afterImport = array(
        'betsyparker@example.com'   => array(
            'addresses' => array('19107', '72701', '19108'),
            'data' => array(
                self::ATTRIBUTE_CODE_FIRST_NAME => 'NotBetsy',
                self::ATTRIBUTE_CODE_LAST_NAME  => 'NotParker',
            ),
        ),
        'anthonyanealy@magento.com' => array('addresses' => array('72701', '92664')),
        'loribbanks@magento.com'    => array('addresses' => array('98801')),
        'kellynilson@magento.com'   => array('addresses' => array()),
    );

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_entityAdapter = $this->_objectManager
            ->create('Magento\ImportExport\Model\Import\Entity\CustomerComposite');
    }

    /**
     * Assertion of current customer and address data
     *
     * @param array $expectedData
     */
    protected function _assertCustomerData(array $expectedData)
    {
        /** @var $collection \Magento\Customer\Model\Resource\Customer\Collection */
        $collection = $this->_objectManager->create('Magento\Customer\Model\Resource\Customer\Collection');
        $collection->addAttributeToSelect($this->_customerAttributes);
        $customers = $collection->getItems();

        $this->assertSameSize($expectedData, $customers);

        /** @var $customer \Magento\Customer\Model\Customer */
        foreach ($customers as $customer) {
            // assert customer existence
            $email = strtolower($customer->getEmail());
            $this->assertArrayHasKey($email, $expectedData);

            // assert customer data (only for required customers)
            if (isset($expectedData[$email]['data'])) {
                foreach ($expectedData[$email]['data'] as $attribute => $expectedValue) {
                    $this->assertEquals($expectedValue, $customer->getData($attribute));
                }
            }

            // assert address data
            $addresses = $customer->getAddresses();
            $this->assertSameSize($expectedData[$email]['addresses'], $addresses);
            /** @var $address \Magento\Customer\Model\Address */
            foreach ($addresses as $address) {
                $this->assertContains($address->getData('postcode'), $expectedData[$email]['addresses']);
            }
        }
    }

    /**
     * @param string $behavior
     * @param string $sourceFile
     * @param array $dataBefore
     * @param array $dataAfter
     * @param array $errors
     *
     * @magentoDataFixture Magento/ImportExport/_files/customers_for_address_import.php
     * @magentoAppIsolation enabled
     *
     * @dataProvider importDataDataProvider
     * @covers \Magento\ImportExport\Model\Import\Entity\CustomerComposite::_importData
     */
    public function testImportData($behavior, $sourceFile, array $dataBefore, array $dataAfter, array $errors = array())
    {
        \Mage::app()->getArea(\Magento\Core\Model\App\Area::AREA_FRONTEND)->load();
        // set entity adapter parameters
        $this->_entityAdapter->setParameters(array('behavior' => $behavior));

        // set fixture CSV file
        $result = $this->_entityAdapter
            ->setSource(\Magento\ImportExport\Model\Import\Adapter::findAdapterFor($sourceFile))
            ->isDataValid();
        if ($errors) {
            $this->assertFalse($result);
        } else {
            $this->assertTrue($result);
        }

        // assert validation errors
        // can't use error codes because entity adapter gathers only error messages from aggregated adapters
        $actualErrors = array_values($this->_entityAdapter->getErrorMessages());
        $this->assertEquals($errors, $actualErrors);

        // assert data before import
        $this->_assertCustomerData($dataBefore);

        // import data
        $this->_entityAdapter->importData();

        // assert data after import
        $this->_assertCustomerData($dataAfter);
    }

    /**
     * Data provider for testImportData
     *
     * @return array
     */
    public function importDataDataProvider()
    {
        $filesDirectory    = __DIR__ . '/_files/';
        $sourceData = array(
            'delete_behavior' => array(
                '$behavior'   => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
                '$sourceFile' => $filesDirectory. self::DELETE_FILE_NAME,
                '$dataBefore' => $this->_beforeImport,
                '$dataAfter'  => array(),
            ),
        );

        $sourceData['add_update_behavior'] = array(
            '$behavior'   => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
            '$sourceFile' => $filesDirectory . self::UPDATE_FILE_NAME,
            '$dataBefore' => $this->_beforeImport,
            '$dataAfter'  => $this->_afterImport,
            '$errors'     => array(array(6)),     // row #6 has no website
        );

        return $sourceData;
    }
}
