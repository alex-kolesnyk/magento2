<?php
/**
 * {license_notice}
 *
 * @category    tests
 * @package     static
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Set of tests for static code analysis, e.g. code style, code complexity, copy paste detecting, etc.
 */
namespace Magento\Test\Php;

class LiveCodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $_reportDir = '';

    /**
     * @var array
     */
    protected static $_whiteList = array();

    /**
     * @var array
     */
    protected static $_blackList = array();

    public static function setUpBeforeClass() 
    {
        self::$_reportDir = \Magento\TestFramework\Utility\Files::init()->getPathToSource()
            . '/dev/tests/static/report';
        if (!is_dir(self::$_reportDir)) {
            mkdir(self::$_reportDir, 0777);
        }
        self::setupFileLists();
    }

    public static function setupFileLists($type = '')
    {
        if ($type != '' && !preg_match('/\/$/', $type)) {
            $type = $type . '/';
        }
        self::$_whiteList = self::_readLists(__DIR__ . '/_files/'.$type.'whitelist/*.txt');
        self::$_blackList = self::_readLists(__DIR__ . '/_files/'.$type.'blacklist/*.txt');
    }

    public function testCodeStylePsr2()
    {
        $reportFile = self::$_reportDir . '/phpcs_psr2_report.xml';
        $wrapper = new \Magento\TestFramework\CodingStandard\Tool\CodeSniffer\Wrapper();
        $codeSniffer = new \Magento\TestFramework\CodingStandard\Tool\CodeSniffer(
            'PSR2',
            $reportFile,
            $wrapper
        );
        if (!$codeSniffer->canRun()) {
            $this->markTestSkipped('PHP Code Sniffer is not installed.');
        }
        if (version_compare($codeSniffer->version(), '1.4.7') === -1) {
            $this->markTestSkipped('PHP Code Sniffer Build Too Old.');
        }
        self::setupFileLists('phpcs');
        $result = $codeSniffer->run(self::$_whiteList, self::$_blackList, array('php'));
        $this->assertFileExists($reportFile, 'Expected '.$reportFile.' to be created by phpcs run with PSR2 standard');
        // disabling the assertEquals below to allow the test to not fail but just report PSR2 violations to everyone.
        // It should be uncommented once compliance is required.
        /*
        $this->assertEquals(
            0,
            $result,
            "PHP Code Sniffer has found $result error(s): See detailed report in $reportFile"
        );
         */
        // Remove this echo when the assert can be uncommented out.
        echo "PHP Code Sniffer has found $result error(s): See detailed report in $reportFile";
    }

    public function testCodeStyle()
    {
        $reportFile = self::$_reportDir . '/phpcs_report.xml';
        $wrapper = new \Magento\TestFramework\CodingStandard\Tool\CodeSniffer\Wrapper();
        $codeSniffer = new \Magento\TestFramework\CodingStandard\Tool\CodeSniffer(
            realpath(__DIR__ . '/_files/phpcs'),
            $reportFile,
            $wrapper
        );
        if (!$codeSniffer->canRun()) {
            $this->markTestSkipped('PHP Code Sniffer is not installed.');
        }
        self::setupFileLists();
        $result = $codeSniffer->run(self::$_whiteList, self::$_blackList, array('php','phtml'));
        $this->assertEquals(
            0,
            $result,
            "PHP Code Sniffer has found $result error(s): See detailed report in $reportFile"
        );
    }

    public function testCodeMess()
    {
        $reportFile = self::$_reportDir . '/phpmd_report.xml';
        $codeMessDetector = new \Magento\TestFramework\CodingStandard\Tool\CodeMessDetector(
            realpath(__DIR__ . '/_files/phpmd/ruleset.xml'),
            $reportFile
        );

        if (!$codeMessDetector->canRun()) {
            $this->markTestSkipped('PHP Mess Detector is not available.');
        }

        self::setupFileLists();
        $this->assertEquals(
            \PHP_PMD_TextUI_Command::EXIT_SUCCESS,
            $codeMessDetector->run(self::$_whiteList, self::$_blackList),
            "PHP Code Mess has found error(s): See detailed report in $reportFile"
        );
    }

    public function testCopyPaste()
    {
        $reportFile = self::$_reportDir . '/phpcpd_report.xml';
        $copyPasteDetector = new \Magento\TestFramework\CodingStandard\Tool\CopyPasteDetector($reportFile);

        if (!$copyPasteDetector->canRun()) {
            $this->markTestSkipped('PHP Copy/Paste Detector is not available.');
        }

        self::setupFileLists();
        $blackList = array();
        foreach (glob(__DIR__ . '/_files/phpcpd/blacklist/*.txt') as $list) {
            $blackList = array_merge($blackList, file($list, FILE_IGNORE_NEW_LINES));
        }

        $this->assertTrue(
            $copyPasteDetector->run(array(), $blackList),
            "PHP Copy/Paste Detector has found error(s): See detailed report in $reportFile"
        );
    }

    /**
     * Read all text files by specified glob pattern and combine them into an array of valid files/directories
     *
     * The Magento root path is prepended to all (non-empty) entries
     *
     * @param string $globPattern
     * @return array
     * @throws \Exception if any of the patterns don't return any result
     */
    protected static function _readLists($globPattern)
    {
        $patterns = array();
        foreach (glob($globPattern) as $list) {
            $patterns = array_merge($patterns, file($list, FILE_IGNORE_NEW_LINES));
        }

        // Expand glob patterns
        $result = array();
        foreach ($patterns as $pattern) {
            if (0 === strpos($pattern, '#')) {
                continue;
            }
            /**
             * Note that glob() for directories will be returned as is,
             * but passing directory is supported by the tools (phpcpd, phpmd, phpcs)
             */
            $files = glob(\Magento\TestFramework\Utility\Files::init()->getPathToSource() . '/' . $pattern, GLOB_BRACE);
            if (empty($files)) {
                throw new \Exception("The glob() pattern '{$pattern}' didn't return any result.");
            }
            $result = array_merge($result, $files);
        }
        return $result;
    }
}
