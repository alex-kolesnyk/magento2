<?php
/**
 * {license_notice}
 *
 * @category    tests
 * @package     static
 * @subpackage  Legacy
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Tests to find various obsolete code usage
 * (deprecated and removed Magento 1 legacy methods, properties, classes, etc.)
 */
class Legacy_ObsoleteCodeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Message text that is used to render suggestions
     */
    const SUGGESTION_MESSAGE = 'Use "%s" instead.';

    /**
     * In-memory cache for the configuration files
     *
     * @var array
     */
    protected static $_configFilesCache = array();

    /**
     * @param string $file
     * @dataProvider phpFileDataProvider
     */
    public function testPhpFile($file)
    {
        $content = file_get_contents($file);
        $this->_testObsoleteClasses($content, $file);
        $this->_testObsoleteMethods($content, $file);
        $this->_testObsoleteMethodArguments($content);
        $this->_testObsoleteProperties($content, $file);
        $this->_testObsoleteActions($content, $file);
        $this->_testObsoleteConstants($content, $file);
        $this->_testObsoletePropertySkipCalculate($content);
    }

    /**
     * @return array
     */
    public function phpFileDataProvider()
    {
        return Utility_Files::getPhpFiles();
    }

    /**
     * @param string $file
     * @dataProvider xmlFileDataProvider
     */
    public function testXmlFile($file)
    {
        $content = file_get_contents($file);
        $this->_testObsoleteClasses($content, $file);
    }

    /**
     * @return array
     */
    public function xmlFileDataProvider()
    {
        return Utility_Files::getXmlFiles();
    }

    /**
     * @param string $file
     * @dataProvider jsFileDataProvider
     */
    public function testJsFile($file)
    {
        $content = file_get_contents($file);
        $this->_testObsoletePropertySkipCalculate($content);
    }

    /**
     * @return array
     */
    public function jsFileDataProvider()
    {
        return Utility_Files::getJsFiles();
    }

    /**
     * @param string $content
     * @param string $file
     */
    protected function _testObsoleteClasses($content, $file)
    {
        $declarations = $this->_getRelevantConfigEntities('obsolete_classes*.php', $content, $file);
        foreach ($declarations as $entity => $suggestion) {
            $this->assertNotRegExp(
                '/[^a-z\d_]' . preg_quote($entity, '/') . '[^a-z\d_]/iS',
                $content,
                "Class '$entity' is obsolete. $suggestion"
            );
        }
    }

    /**
     * @param string $content
     * @param string $file
     */
    protected function _testObsoleteMethods($content, $file)
    {
        $declarations = $this->_getRelevantConfigEntities('obsolete_methods*.php', $content, $file);
        foreach ($declarations as $method => $suggestion) {
            $this->assertNotRegExp('/[^a-z\d_]' . preg_quote($method, '/') . '\s*\(/iS',
                $content, "Method '$method' is obsolete. $suggestion"
            );
        }
    }

    /**
     * @param string $content
     */
    protected function _testObsoleteMethodArguments($content)
    {
        $this->assertNotRegExp('/[^a-z\d_]getTypeInstance\s*\(\s*[^\)]+/iS', $content,
            'Backwards-incompatible change: method getTypeInstance() is not supposed to be invoked with any arguments.'
        );
        $this->assertNotRegExp('/\->getUsedProductIds\(([^\)]+,\s*[^\)]+)?\)/', $content,
            'Backwards-incompatible change: method getUsedProductIds($product)'
            . ' must be invoked with one and only one argument - product model object'
        );
    }

    /**
     * @param string $content
     * @param string $file
     */
    protected function _testObsoleteProperties($content, $file)
    {
        $declarations = $this->_getRelevantConfigEntities('obsolete_properties*.php', $content, $file);
        foreach ($declarations as $entity => $suggestion) {
            $this->assertNotRegExp(
                '/[^a-z\d_]' . preg_quote($entity, '/') . '[^a-z\d_]/iS',
                $content,
                "Property '$entity' is obsolete. $suggestion"
            );
        }
    }

    /**
     * @param string $content
     */
    protected function _testObsoleteActions($content)
    {
        $suggestion = 'Resizing images upon the client request is obsolete, use server-side resizing instead';
        $this->assertNotRegExp(
            '#[^a-z\d_/]catalog/product/image[^a-z\d_/]#iS',
            $content,
            "Action 'catalog/product/image' is obsolete. $suggestion"
        );
    }

    /**
     * @param string $content
     * @param string $file
     */
    protected function _testObsoleteConstants($content, $file)
    {
        $declarations = $this->_getRelevantConfigEntities('obsolete_constants*.php', $content, $file);
        foreach ($declarations as $entity => $suggestion) {
            $this->assertNotRegExp(
                '/[^a-z\d_]' . preg_quote($entity, '/') . '[^a-z\d_]/iS',
                $content,
                "Constant '$entity' is obsolete. $suggestion"
            );
        }
    }

    /**
     * @param string $content
     */
    protected function _testObsoletePropertySkipCalculate($content)
    {
        $this->assertNotRegExp(
            '/[^a-z\d_]skipCalculate[^a-z\d_]/iS',
            $content,
            "Configuration property 'skipCalculate' is obsolete."
        );
    }

    /**
     * Retrieve configuration items, whose 'class_scope' match to the content, in the following format:
     *   array(
     *     '<entity>' => '<suggestion>',
     *     ...
     *   )
     *
     * @param string $fileNamePattern
     * @param string $content
     * @param string $file
     * @return array
     */
    protected function _getRelevantConfigEntities($fileNamePattern, $content, $file)
    {
        $result = array();
        foreach ($this->_loadConfigFiles($fileNamePattern) as $entity => $info) {
            $class = $info['class_scope'];
            $regexp = '/(class|extends)\s+' . preg_quote($class, '/') . '(\s|;)/S';
            /* Note: strpos is used just to prevent excessive preg_match calls */
            if ($class && (!strpos($content, $class) || !preg_match($regexp, $content))) {
                continue;
            }
            if ($info['directory']) {
                if (0 !== strpos(str_replace('\\', '/', $file), str_replace('\\', '/', $info['directory']))) {
                    continue;
                }
            }
            $result[$entity] = $info['suggestion'];
        }
        return $result;
    }

    /**
     * Load configuration data from the files that match a glob-pattern
     *
     * @param string $fileNamePattern
     * @return array
     */
    protected function _loadConfigFiles($fileNamePattern)
    {
        if (isset(self::$_configFilesCache[$fileNamePattern])) {
            return self::$_configFilesCache[$fileNamePattern];
        }
        $config = array();
        foreach (glob(dirname(__FILE__) . '/_files/' . $fileNamePattern, GLOB_BRACE) as $configFile) {
            $config = array_merge($config, include($configFile));
        }
        $result = $this->_normalizeConfigData($config);
        self::$_configFilesCache[$fileNamePattern] = $result;
        return $result;
    }

    /**
     * Convert config data to the uniform format:
     *   array(
     *     '<entity>' => array(
     *       'suggestion' => '<suggestion>',
     *       'class_scope' => '<class_scope>',
     *     ),
     *     ...
     *   )
     *
     * @param array $config
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _normalizeConfigData(array $config)
    {
        $result = array();
        foreach ($config as $key => $value) {
            $entity = is_string($key) ? $key : $value;
            $result[$entity] = array(
                'suggestion'  => isset($value['suggestion'])
                    ? sprintf(self::SUGGESTION_MESSAGE, $value['suggestion']) : null,
                'class_scope' => isset($value['class_scope']) ? $value['class_scope'] : null,
                'directory'   => isset($value['directory']) ? PATH_TO_SOURCE_CODE . '/' . $value['directory'] : null,
            );
        }
        return $result;
    }
}
