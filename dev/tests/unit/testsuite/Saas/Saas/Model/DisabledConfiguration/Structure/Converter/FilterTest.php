<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Saas_Saas_Model_DisabledConfiguration_Structure_Converter_FilterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider convertDataProvider
     */
    public function testConvert($baseConfig, $restrictedOptions, $expected)
    {
        $map = $this->getMockForAbstractClass('Mage_Backend_Model_Config_Structure_MapperAbstract');
        $map->expects($this->any())
            ->method('map')
            ->will($this->returnValue($baseConfig));

        $mapperFactory = $this->getMock('Mage_Backend_Model_Config_Structure_Mapper_Factory', array(), array(), '',
            false);
        $mapperFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($map));

        $config = new Saas_Saas_Model_DisabledConfiguration_Config($restrictedOptions);
        $model = new Saas_Saas_Model_DisabledConfiguration_Structure_Converter_Filter($mapperFactory, $config);
        $domDocument = dom_import_simplexml(simplexml_load_string('<config><system></system></config>'));
        $result = $model->convert($domDocument);
        $this->assertEquals($expected, $result);
    }

    public static function convertDataProvider()
    {
        // Normal convert
        $baseConfig = array(
            'config' => array(
                'system' => array(
                    'sections' => array(
                        'section_allowed' => array(
                            'children' => array(
                                'group_allowed' => array(
                                    'children' => array('field_allowed' => array()),
                                ),
                                'group_restricted' => array(
                                    'children' => array(
                                        'field_allowed' => array(),
                                        'field_restricted' => array()
                                    ),
                                ),
                            ),
                        ),
                        'section_restricted' => array(
                            'children' => array(
                                'group' => array(
                                    'children' => array('field' => null,),
                                ),
                            ),
                        ),
                    ),
                ),
                'non_system' => null,
            ),
        );
        $expected = array(
            'config' => array(
                'system' => array(
                    'sections' => array(
                        'section_allowed' => array(
                            'children' => array(
                                'group_allowed' => array(
                                    'children' => array('field_allowed' => array()),
                                ),
                            ),
                        ),
                    ),
                ),
                'non_system' => null,
            ),
        );
        $restrictedOptions = array(
            'section_restricted',
            'section_allowed/group_restricted',
            'section_allowed/group_allowed/field_restricted'
        );

        // No sections
        $baseConfigNoSections = $baseConfig;
        unset($baseConfigNoSections['config']['system']['sections']);
        $expectedNoSections = $expected;
        unset($expectedNoSections['config']['system']['sections']);

        // No groups
        $baseConfigNoGroups = $baseConfig;
        unset($baseConfigNoGroups['config']['system']['sections']['section_allowed']['children']);
        unset($baseConfigNoGroups['config']['system']['sections']['section_restricted']['children']);
        $expectedNoGroups = $expected;
        unset($expectedNoGroups['config']['system']['sections']['section_allowed']['children']);

        // No fields
        $baseConfigNoFields = $baseConfig;
        unset($baseConfigNoFields['config']['system']['sections']['section_allowed']['children']['group_allowed']
            ['children']);
        unset($baseConfigNoFields['config']['system']['sections']['section_allowed']['children']['group_restricted']
            ['children']);
        unset($baseConfigNoFields['config']['system']['sections']['section_restricted']['children']['group_allowed']
            ['children']);
        $expectedNoFields = $expected;
        unset($expectedNoFields['config']['system']['sections']['section_allowed']['children']['group_allowed']
            ['children']);

        // Data sets
        return array(
            'normal convert' => array($baseConfig, $restrictedOptions, $expected),
            'no sections' => array($baseConfigNoSections, $restrictedOptions, $expectedNoSections),
            'no groups' => array($baseConfigNoGroups, $restrictedOptions, $expectedNoGroups),
            'no fields' => array($baseConfigNoFields, $restrictedOptions, $expectedNoFields),
        );
    }
}
