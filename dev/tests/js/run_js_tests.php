<?php
/**
 * This script executes all Magento JavaScript unit tests.
 *
 * {license_notice}
 *
 * @category    tests
 * @package     js
 * @copyright   {copyright}
 * @license     {license_link}
 */

define('RELATIVE_APP_ROOT', '../../..');
require __DIR__ . '/../../../app/autoload.php';
Magento_Autoload_IncludePath::addIncludePath(realpath(RELATIVE_APP_ROOT . '/lib'));

$userConfig = normalize('jsTestDriver.php');
$defaultConfig = normalize('jsTestDriver.php.dist');

$configFile = file_exists($userConfig) ? $userConfig : $defaultConfig;
$config = require($configFile);

if (isset($config['JsTestDriver'])) {
    $jsTestDriver = $config['JsTestDriver'];
} else {
    echo "Value for the 'JsTestDriver' configuration parameter is not specified." . PHP_EOL;
    showUsage();
}
if (!file_exists($jsTestDriver)) {
    reportError('JsTestDriver jar file does not exist: ' . $jsTestDriver);
}

if (isset($config['Browser'])) {
    $browser = $config['Browser'];
} else {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $browser = 'C:\Program Files (x86)\Mozilla Firefox\firefox.exe';
    } else {
        $browser = exec('which firefox');
    }
}
if (!file_exists($browser)) {
    reportError('Browser executable not found: ' . $browser);
}

$server = isset($config['server']) ? $config['server'] : "http://localhost:9876";
$port = substr(strrchr($server, ':'), 1);

$proxies = isset($config['proxy']) ? $config['proxy'] : array();

$testFilesPath = isset($config['test']) ? $config['test'] : array();
$testFiles = listFiles($testFilesPath);

$loadFilesPath = isset($config['load']) ? $config['load'] : array();
$loadFiles = listFiles($loadFilesPath);
if (empty($loadFiles)) {
    reportError('Could not find any files to load.');
}

$serveFilesPath = isset($config['serve']) ? $config['serve'] : array();
$serveFiles = listFiles($serveFilesPath);

$sortedFiles = array();

$fileOrder = normalize('jsTestDriverOrder.php');
if (file_exists($fileOrder)) {
    $loadOrder = require($fileOrder);
    foreach ($loadOrder as $file) {
        $sortedFiles[] = RELATIVE_APP_ROOT . $file;
    }
    foreach ($loadFiles as $loadFile) {
        $found = false;
        $normalizedLoadFile = normalize($loadFile);
        foreach ($loadOrder as $orderFile) {
            if (strcmp(normalize(RELATIVE_APP_ROOT . $orderFile), $normalizedLoadFile) == 0) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            array_push($sortedFiles, $loadFile);
        }
    }
}

$jsTestDriverConf = __DIR__ . '/jsTestDriver.conf';
$fh = fopen($jsTestDriverConf, 'w');

fwrite($fh, "server: $server" . PHP_EOL);

if (count($proxies) > 0) {
    fwrite($fh, "proxy:" . PHP_EOL);
    foreach ($proxies as $proxy) {
        $proxyServer = sprintf($proxy['server'], $server, normalize(RELATIVE_APP_ROOT));
        fwrite($fh, '  - {matcher: "' . $proxy['matcher'] . '", server: "' . $proxyServer . '"}' . PHP_EOL);
    }
}

fwrite($fh, "load:" . PHP_EOL);
foreach ($sortedFiles as $file) {
    if (!in_array($file, $serveFiles)) {
        fwrite($fh, "  - " . $file . PHP_EOL);
    }
}

fwrite($fh, "test:" . PHP_EOL);
foreach ($testFiles as $file) {
    fwrite($fh, "  - " . $file . PHP_EOL);
}

if (count($serveFiles) > 0) {
    fwrite($fh, "serve:" . PHP_EOL);
    foreach ($serveFiles as $file) {
        fwrite($fh, "  - " . $file . PHP_EOL);
    }
}

fclose($fh);

$testOutput = __DIR__ . '/test-output';
Varien_Io_File::rmdirRecursive($testOutput);
mkdir($testOutput);

$command
    = 'java -jar "' . $jsTestDriver . '" --config "' . $jsTestDriverConf . '" --port ' . $port .
    ' --browser "' . $browser . '" --tests all --testOutput "' . $testOutput . '"';

echo $command . PHP_EOL;

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    system($command);
} else {
    $shellCommand
        = '#!/bin/bash
        LSOF=`/usr/sbin/lsof -i :' . $port . ' -t`
        if [ "$LSOF" != "" ];
        then
            kill -9 $LSOF
        fi

        pkill Xvfb
        XVFB=`which Xvfb`
        if [ "$?" -eq 1 ];
        then
            echo "Xvfb not found."
            exit 1
        fi

        $XVFB :99 -nolisten inet6 -screen 0 1024x768x24 -ac &
        PID_XVFB="$!"         # take the process ID
        export DISPLAY=:99.0  # set display to use that of the Xvfb

        # run the tests
        ' . $command . '

        kill $PID_XVFB       # shut down Xvfb (firefox will shut down cleanly by JsTestDriver)
        echo "Done."';

    system($shellCommand);
}

/**
 * Show a message that displays how to use (invoke) this PHP script and exit.
 */
function showUsage()
{
    reportError('Usage: php run_js_tests.php');
}

/**
 * Reports an error given an error message and exits, effectively halting the PHP script's execution.
 *
 * @param string $message - Error message to be displayed to the user.
 *
 * @SuppressWarnings(PHPMD.ExitExpression)
 */
function reportError($message)
{
    echo $message . PHP_EOL;
    exit(1);
}

/**
 * Takes a file or directory path in any form and normalizes it to fully absolute canonical form
 * relative to this PHP script's location.
 *
 * @param string $filePath - File or directory path to be fully normalized to canonical form.
 *
 * @return string - The fully resolved path converted to absolute form.
 */
function normalize($filePath)
{
    return str_replace('\\', '/', realpath(__DIR__ . '/' . $filePath));
}

/**
 * Accepts an array of directories and generates a list of Javascript files (.js) in those directories and
 * all subdirectories recursively.
 *
 * @param array $dirs - An array of directories as specified in the configuration file (i.e. $configFile).
 *
 * @return array - An array of directory paths to all Javascript files found by recursively searching the
 * specified array of directories.
 */
function listFiles($dirs)
{
    $baseDir = normalize(RELATIVE_APP_ROOT);
    $result = array();
    foreach ($dirs as $dir) {
        $path = $baseDir . $dir;
        if (is_file($path)) {
            $path = substr_replace($path, RELATIVE_APP_ROOT, 0, strlen($baseDir));
            array_push($result, $path);
        } else {
            $paths = glob($path . '/*', GLOB_ONLYDIR | GLOB_NOSORT);
            $paths = substr_replace($paths, '', 0, strlen($baseDir));
            $result = array_merge($result, listFiles($paths));

            $files = glob($path . '/*.js', GLOB_NOSORT);
            $files = substr_replace($files, RELATIVE_APP_ROOT, 0, strlen($baseDir));
            $result = array_merge($result, $files);
        }
    }
    return $result;
}
