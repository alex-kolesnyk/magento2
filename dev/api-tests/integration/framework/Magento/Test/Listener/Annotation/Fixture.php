<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Implementation of the @magentoDataFixture doc comment directive
 */
class Magento_Test_Listener_Annotation_Fixture
{
    /**
     * @var Magento_Test_Listener
     */
    protected $_listener;

    /**
     * Fixtures that have been applied
     *
     * @var array
     */
    private $_appliedFixtures = array();

    /**
     * Constructor
     *
     * @param Magento_Test_Listener $listener
     */
    public function __construct(Magento_Test_Listener $listener)
    {
        $this->_listener = $listener;
    }

    /**
     * Handler for 'endTestSuite' event
     */
    public function endTestSuite()
    {
        $this->_revertFixtures();
    }

    /**
     * Handler for 'startTest' event
     */
    public function startTest()
    {
        /* Apply fixtures declared on test case (class) and test (method) levels */
        $methodFixtures = $this->_getFixtures('method');
        if ($methodFixtures) {
            /* Re-apply even the same fixtures to guarantee data consistency */
            $this->_revertFixtures();
            $this->_applyFixtures($methodFixtures);
        } else {
            $this->_applyFixtures($this->_getFixtures('class'));
        }
    }

    /**
     * Handler for 'endTest' event
     */
    public function endTest()
    {
        /* Isolate other tests from test-specific fixtures */
        $methodFixtures = $this->_getFixtures('method');
        if ($methodFixtures) {
            $this->_revertFixtures();
        }
    }

    /**
     * Retrieve fixtures from annotation
     *
     * @param string $scope 'class' or 'method'
     * @return array
     */
    protected function _getFixtures($scope)
    {
        $annotations = $this->_listener->getCurrentTest()->getAnnotations();
        if (!empty($annotations[$scope]['magentoDataFixture'])) {
            return $annotations[$scope]['magentoDataFixture'];
        }
        return array();
    }

    /**
     * Check whether the same connection is being used for both read and write operations
     *
     * @return bool
     */
    protected function _isSingleConnection()
    {
        $readAdapter  = Mage::getSingleton('Mage_Core_Model_Resource')->getConnection('read');
        $writeAdapter = Mage::getSingleton('Mage_Core_Model_Resource')->getConnection('write');
        return ($readAdapter === $writeAdapter);
    }

    /**
     * Start transaction
     */
    protected function _startTransaction()
    {
        /** @var $adapter Varien_Db_Adapter_Interface */
        $adapter = Mage::getSingleton('Mage_Core_Model_Resource')->getConnection('write');

        //TODO: validate
        $transactionLevel = $adapter->getTransactionLevel();
        if($transactionLevel != 0) $adapter->commit();

        // check isolation level
        if(defined('TESTS_FIXTURE_TRANSACTION') && (TESTS_TRANSACTION_ISOLATION_LEVEL === 'READ UNCOMMITTED')){
            $adapter->query('SET GLOBAL TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;');
        }
        // check if transaction enabled
        if(!defined('TESTS_FIXTURE_TRANSACTION') || TESTS_FIXTURE_TRANSACTION == 'on'){
            $adapter->beginTransaction();
        }

    }

    /**
     * Rollback transaction
     */
    protected function _rollbackTransaction()
    {
        /** @var $adapter Varien_Db_Adapter_Interface */
        $adapter = Mage::getSingleton('Mage_Core_Model_Resource')->getConnection('write');

        if(!defined('TESTS_FIXTURE_TRANSACTION') || TESTS_FIXTURE_TRANSACTION == 'on'){
            $adapter->rollBack();
        }

    }

    /**
     * Execute single fixture script
     *
     * @param string|array $fixture
     */
    protected function _applyOneFixture($fixture)
    {
        if (is_callable($fixture)) {
            call_user_func($fixture);
        } else {
            require($fixture);
        }
    }

    /**
     * Execute fixture scripts if any
     *
     * @param array $fixtures
     * @throws Magento_Exception
     */
    protected function _applyFixtures(array $fixtures)
    {
        if (empty($fixtures)) {
            return;
        }
        /* Start transaction before applying first fixture to be able to revert them all further */
        if (empty($this->_appliedFixtures)) {
            if (!$this->_isSingleConnection()) {
                throw new Magento_Exception('Transaction fixtures with 2 connections are not implemented yet.');
            }
            $this->_startTransaction();
        }
        /* Execute fixture scripts */
        foreach ($fixtures as $fixture) {
            if (strpos($fixture, '\\') !== false) {
                throw new Magento_Exception('The "\" symbol is not allowed for fixture definition.');
            }
            $fixtureMethod = array(get_class($this->_listener->getCurrentTest()), $fixture);
            /* Define path to fixture */
            $testsDir = dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS . '..';
            $pathToLocalFixtures = realpath($testsDir . DS . 'testsuite');
            $pathToGlobalFixtures = realpath($testsDir . DS . 'fixture');
            $fixtureScript = file_exists($pathToLocalFixtures . DS . $fixture)
                ? $pathToLocalFixtures . DS . $fixture
                : $pathToGlobalFixtures . DS . $fixture;
            /* Skip already applied fixtures */
            if (in_array($fixtureMethod, $this->_appliedFixtures, true)
                || in_array($fixtureScript, $this->_appliedFixtures, true)
            ) {
                continue;
            }
            if (is_callable($fixtureMethod)) {
                $this->_applyOneFixture($fixtureMethod);
                $this->_appliedFixtures[] = $fixtureMethod;
            } else {
                $this->_applyOneFixture($fixtureScript);
                $this->_appliedFixtures[] = $fixtureScript;
            }
        }
    }

    /**
     * Revert changes done by fixtures
     */
    protected function _revertFixtures()
    {
        if (empty($this->_appliedFixtures)) {
            return;
        }
        $this->_rollbackTransaction();
        foreach ($this->_appliedFixtures as $fixture) {
            if (is_callable($fixture)) {
                $fixture[1] .= 'Rollback';
                if (is_callable($fixture)) {
                    $this->_applyOneFixture($fixture);
                }
            } else {
                $fileInfo = pathinfo($fixture);
                $extension = '';
                if (isset($fileInfo['extension'])) {
                    $extension = '.' . $fileInfo['extension'];
                }
                $rollbackFile = $fileInfo['dirname'] . '/' . $fileInfo['filename'] . '_rollback' . $extension;
                if (file_exists($rollbackFile)) {
                    $this->_applyOneFixture($rollbackFile);
                }
            }
        }
        $this->_appliedFixtures = array();
    }
}
