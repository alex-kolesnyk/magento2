<?php
/**
 * Magento_Install_Model_Config
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Magento_Install_Model_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Install_Model_Config
     */
    private $_object;

    public function setUp()
    {
        /** @var $cacheTypeList Magento_Core_Model_Cache_TypeListInterface */
        $cacheTypeList = Mage::getModel('Magento_Core_Model_Cache_TypeListInterface');
        $types = array_keys($cacheTypeList->getTypes());

        /** @var $cacheState Magento_Core_Model_Cache_StateInterface */
        $cacheState = Magento_TestFramework_Helper_Bootstrap::getObjectManager()
            ->get('Magento_Core_Model_Cache_StateInterface');
        foreach ($types as $type) {
            $cacheState->setEnabled($type, false);
        }
        $cacheState->persist();

        /** @var Magento_Core_Model_Dir $dirs */
        $dirs = Mage::getObjectManager()->create(
            'Magento_Core_Model_Dir', array(
                'baseDir' => BP,
                'dirs' => array(
                    Magento_Core_Model_Dir::MODULES => __DIR__ . '/_files',
                    Magento_Core_Model_Dir::CONFIG => __DIR__ . '/_files'
                )
            )
        );

        /** @var Magento_Core_Model_Module_Declaration_FileResolver $modulesDeclarations */
        $modulesDeclarations = Mage::getObjectManager()->create(
            'Magento_Core_Model_Module_Declaration_FileResolver', array(
                'applicationDirs' => $dirs,
            )
        );


        /** @var Magento_Core_Model_Module_Declaration_Reader_Filesystem $filesystemReader */
        $filesystemReader = Mage::getObjectManager()->create(
            'Magento_Core_Model_Module_Declaration_Reader_Filesystem', array(
                'fileResolver' => $modulesDeclarations,
            )
        );

        /** @var Magento_Core_Model_ModuleList $modulesList */
        $modulesList = Mage::getObjectManager()->create(
            'Magento_Core_Model_ModuleList', array(
                'reader' => $filesystemReader,
            )
        );

        /** @var Magento_Core_Model_Config_Modules_Reader $moduleReader */
        $moduleReader = Mage::getObjectManager()->create(
            'Magento_Core_Model_Config_Modules_Reader', array(
                'dirs' => $dirs,
                'moduleList' => $modulesList
            )
        );

        /** @var Magento_Core_Model_Config_FileResolver $fileResolver */
        $fileResolver = Mage::getObjectManager()->create(
            'Magento_Core_Model_Config_FileResolver', array(
                'moduleReader' => $moduleReader,
            )
        );

        /** @var Magento_Logging_Model_Config_Reader $configReader */
        $configReader = Mage::getObjectManager()->create(
            'Magento_Install_Model_Config_Reader', array(
                'fileResolver' => $fileResolver,
            )
        );

        $configData =  Mage::getObjectManager()->create(
            'Magento_Install_Model_Config_Data', array(
                'reader' => $configReader,
            )
        );

        $this->_object =  Mage::getObjectManager()->create(
            'Magento_Install_Model_Config', array(
                'dataStorage' => $configData,
            )
        );
    }

    public function testGetWizardSteps()
    {
        $steps = $this->_object->getWizardSteps();
        $this->assertEquals(2, count($steps));
        $this->assertCount(2, $steps);
        $this->assertEquals(array('begin', 'locale'), array($steps[0]->getName(), $steps[1]->getName()));
    }

    public function testGetWritableFullPathsForCheck()
    {
        $directories = $this->_object->getWritableFullPathsForCheck();
        $this->assertEquals(2, count($directories));
        $this->assertCount(2, $directories);
        $this->assertEquals('1', $directories['etc']['existence']);
        $this->assertEquals('0', $directories['etc']['recursive']);
        $this->assertTrue(array_key_exists('path', $directories['etc']));
        $this->assertEquals('1', $directories['var']['existence']);
        $this->assertEquals('1', $directories['var']['recursive']);
        $this->assertTrue(array_key_exists('path', $directories['var']));
    }

    public function testGetPathForCheck()
    {
        $directories = $this->_object->getPathForCheck();
        $this->assertEquals(2, count($directories['writeable']));
        $this->assertCount(2, $directories['writeable']);
        $expected = array(
            array(
                'existence' => '1',
                'recursive' => '0'
            ),
            array(
                'existence' => '1',
                'recursive' => '1'
            ),
        );
        $this->assertEquals($expected, $directories['writeable']);

    }
}
