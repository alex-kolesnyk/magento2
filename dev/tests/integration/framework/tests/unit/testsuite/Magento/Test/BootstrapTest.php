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
 * Test class for Magento_Test_Bootstrap.
 */
class Magento_Test_BootstrapTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Test_Bootstrap|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    /**
     * @var Magento_Test_Bootstrap_Settings
     */
    protected $_settings;

    /**
     * @var Magento_Test_Bootstrap_Environment|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_envBootstrap;

    /**
     * @var Magento_Test_Bootstrap_DocBlock|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_docBlockBootstrap;

    /**
     * @var Magento_Test_Bootstrap_Profiler|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_profilerBootstrap;

    /**
     * @var Magento_Test_Bootstrap_Memory
     */
    protected $_memoryBootstrap;

    /**
     * @var Magento_Shell|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shell;

    /**
     * @var string
     */
    protected $_integrationTestsDir;

    protected function setUp()
    {
        $this->_integrationTestsDir = realpath(__DIR__ . '/../../../../../../');
        $this->_settings = new Magento_Test_Bootstrap_Settings($this->_integrationTestsDir, array());
        $this->_envBootstrap = $this->getMock(
            'Magento_Test_Bootstrap_Environment', array('emulateHttpRequest', 'emulateSession')
        );
        $this->_docBlockBootstrap = $this->getMock(
            'Magento_Test_Bootstrap_DocBlock', array('registerAnnotations'), array(__DIR__)
        );
        $profilerDriver = $this->getMock('Magento_Profiler_Driver_Standard', array('registerOutput'));
        $this->_profilerBootstrap = $this->getMock(
            'Magento_Test_Bootstrap_Profiler', array('registerFileProfiler', 'registerBambooProfiler'),
            array($profilerDriver)
        );
        $this->_memoryBootstrap = $this->getMock(
            'Magento_Test_Bootstrap_Memory', array('activateStatsDisplaying', 'activateLimitValidation'),
            array(), '', false
        );
        $this->_shell = $this->getMock('Magento_Shell', array('execute'));
        $this->_object = new Magento_Test_Bootstrap(
            $this->_settings, $this->_envBootstrap, $this->_docBlockBootstrap, $this->_profilerBootstrap,
            $this->_shell, __DIR__
        );
    }

    protected function tearDown()
    {
        $this->_object = null;
        $this->_settings = null;
        $this->_envBootstrap = null;
        $this->_docBlockBootstrap = null;
        $this->_profilerBootstrap = null;
        $this->_memoryBootstrap = null;
        $this->_shell = null;
    }

    /**
     * @param array $fixtureSettings
     * @return Magento_Test_Application|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _injectApplicationMock(array $fixtureSettings = array())
    {
        $application = $this->getMock(
            'Magento_Test_Application', array('cleanup', 'isInstalled', 'initialize', 'install'), array(), '', false
        );
        $settings = new Magento_Test_Bootstrap_Settings($this->_integrationTestsDir, $fixtureSettings);
        // prevent calling the constructor because of mocking the method it invokes
        $this->_object = $this->getMock(
            'Magento_Test_Bootstrap', array('_createApplication', '_createMemoryBootstrap'), array(), '', false
        );
        $this->_object
            ->expects($this->any())
            ->method('_createApplication')
            ->will($this->returnValue($application))
        ;
        // invoke the constructor explicitly
        $this->_object->__construct(
            $settings, $this->_envBootstrap, $this->_docBlockBootstrap, $this->_profilerBootstrap,
            $this->_shell, __DIR__
        );
        $this->_object
            ->expects($this->any())
            ->method('_createMemoryBootstrap')
            ->will($this->returnValue($this->_memoryBootstrap))
        ;
        return $application;
    }

    public function testGetApplication()
    {
        $application = $this->_object->getApplication();
        $this->assertInstanceOf('Magento_Test_Application', $application);
        $this->assertStringStartsWith(__DIR__ . '/sandbox-mysql-', $application->getInstallDir());
        $this->assertInstanceOf('Magento_Test_Db_Mysql', $application->getDbInstance());
        $this->assertSame($application, $this->_object->getApplication());
    }

    public function testGetDbVendorName()
    {
        $this->assertEquals('mysql', $this->_object->getDbVendorName());
    }

    public function testRunBootstrapEnvironment()
    {
        $this->_injectApplicationMock();
        $this->_envBootstrap
            ->expects($this->once())
            ->method('emulateHttpRequest')
            ->with($this->identicalTo($_SERVER))
        ;
        $this->_envBootstrap
            ->expects($this->once())
            ->method('emulateSession')
            ->with($this->identicalTo(isset($_SESSION) ? $_SESSION : null))
        ;
        $this->_object->runBootstrap();
    }

    public function testRunBootstrapProfilerDisabled()
    {
        $this->_injectApplicationMock();
        $this->_profilerBootstrap
            ->expects($this->never())
            ->method($this->anything())
        ;
        $this->_object->runBootstrap();
    }

    public function testRunBootstrapProfilerEnabled()
    {
        $baseDir = $this->_integrationTestsDir;
        $dirSep = DIRECTORY_SEPARATOR;
        $this->_injectApplicationMock(array(
            'TESTS_PROFILER_FILE'                   => 'profiler.csv',
            'TESTS_BAMBOO_PROFILER_FILE'            => 'profiler_bamboo.csv',
            'TESTS_BAMBOO_PROFILER_METRICS_FILE'    => 'profiler_metrics.php',
        ));
        $this->_profilerBootstrap
            ->expects($this->once())
            ->method('registerFileProfiler')
            ->with("{$baseDir}{$dirSep}profiler.csv")
        ;
        $this->_profilerBootstrap
            ->expects($this->once())
            ->method('registerBambooProfiler')
            ->with("{$baseDir}{$dirSep}profiler_bamboo.csv", "{$baseDir}{$dirSep}profiler_metrics.php")
        ;
        $this->_object->runBootstrap();
    }

    public function testRunBootstrapMemoryWatch()
    {
        $this->_injectApplicationMock(array(
            'TESTS_MEM_USAGE_LIMIT' => 100,
            'TESTS_MEM_LEAK_LIMIT'  => 60,
        ));
        $this->_object
            ->expects($this->once())
            ->method('_createMemoryBootstrap')
            ->with(100, 60)
            ->will($this->returnValue($this->_memoryBootstrap))
        ;
        $this->_memoryBootstrap
            ->expects($this->once())
            ->method('activateStatsDisplaying')
        ;
        $this->_memoryBootstrap
            ->expects($this->once())
            ->method('activateLimitValidation')
        ;
        $this->_object->runBootstrap();
    }

    public function testRunBootstrapDocBlockAnnotations()
    {
        $this->_injectApplicationMock();
        $this->_docBlockBootstrap
            ->expects($this->once())
            ->method('registerAnnotations')
            ->with($this->isInstanceOf('Magento_Test_Application'))
        ;
        $this->_object->runBootstrap();
    }

    public function testRunBootstrapAppCleanup()
    {
        $application = $this->_injectApplicationMock(array(
            'TESTS_CLEANUP' => 'enabled',
        ));
        $application
            ->expects($this->once())
            ->method('cleanup')
        ;
        $this->_object->runBootstrap();
    }

    public function testRunBootstrapAppInitialize()
    {
        $application = $this->_injectApplicationMock();
        $application
            ->expects($this->once())
            ->method('isInstalled')
            ->will($this->returnValue(true))
        ;
        $application
            ->expects($this->once())
            ->method('initialize')
        ;
        $application
            ->expects($this->never())
            ->method('install')
        ;
        $application
            ->expects($this->never())
            ->method('cleanup')
        ;
        $this->_object->runBootstrap();
    }

    public function testRunBootstrapAppInstall()
    {
        $adminUserName = Magento_Test_Bootstrap::ADMIN_NAME;
        $adminPassword = Magento_Test_Bootstrap::ADMIN_PASSWORD;
        $adminRoleName = Magento_Test_Bootstrap::ADMIN_ROLE_NAME;
        $application = $this->_injectApplicationMock();
        $application
            ->expects($this->once())
            ->method('isInstalled')
            ->will($this->returnValue(false))
        ;
        $application
            ->expects($this->once())
            ->method('install')
            ->with($adminUserName, $adminPassword, $adminRoleName)
        ;
        $application
            ->expects($this->never())
            ->method('initialize')
        ;
        $application
            ->expects($this->never())
            ->method('cleanup')
        ;
        $this->_object->runBootstrap();
    }

    public function testGetInitParams()
    {
        $initParams = $this->_bootstrap->getInitParams();
        $this->_bootstrap->expects($this->once())
            ->method('_initialize')
            ->with($initParams);
        $this->_bootstrap->expects($this->once())
            ->method('_isInstalled')
            ->will($this->returnValue(true));

        $this->_callBootstrapConstructor();
    }
}
