#!/usr/bin/php
<?php
/**
 * {license_notice}
 *
 * @category   build
 * @package    extruder
 * @copyright  {copyright}
 * @license    {license_link}
 */

require __DIR__ . '/../../../../lib/Magento/Shell.php';

define('USAGE', <<<USAGE
$>./extruder.php -w <working_dir> -l /path/to/common.txt [[-l /path/to/extra.txt] parameters]
    additional parameters:
    -w dir  directory with working copy to edit with the extruder
    -l      one or many files with lists that refer to files and directories to be deleted
    -v      additional verbosity in output

USAGE
);

$shortOpts = 'l:w:gdvi';
$options = getopt($shortOpts);

try {
    // working dir argument
    if (empty($options['w'])) {
        throw new Exception(USAGE);
    }
    $workingDir = realpath($options['w']);
    if (!$workingDir  || !is_writable($workingDir) || !is_dir($workingDir)) {
        throw new Exception("'{$options['w']}' must be a writable directory.");
    }

    // lists argument
    if (empty($options['l'])) {
        throw new Exception(USAGE);
    }
    if (!is_array($options['l'])) {
        $options['l'] = array($options['l']);
    }
    $list = array();
    foreach ($options['l'] as $file) {
        $patterns = file($file, FILE_IGNORE_NEW_LINES);
        foreach ($patterns as $pattern) {
            if (empty($pattern) || 0 === strpos($pattern, '#')) { // comments start from #
                continue;
            }
            $pattern = $workingDir . DIRECTORY_SEPARATOR . $pattern;
            $items = glob($pattern, GLOB_BRACE);
            if (empty($items)) {
                throw new Exception("glob() pattern '{$pattern}' returned empty result.");
            }
            $list = array_merge($list, $items);
        }
    }
    if (empty($list)) {
        throw new Exception('List of files or directories to delete is empty.');
    }

    // verbosity argument
    $verbose = isset($options['v']);

    // perform "extrusion"
    $shell = new Magento_Shell($verbose);
    foreach ($list as $item) {
        if (!file_exists($item)) {
            throw new Exception("The file or directory '{$item} is marked for deletion, but it doesn't exist.");
        }
        $shell->execute(
            'git --git-dir %s --work-tree %s rm -r -f -- %s',
            array("{$workingDir}/.git", $workingDir, $item)
        );
        if (file_exists($item)) {
            throw new Exception("The file or directory '{$item}' was supposed to be deleted, but it still exists.");
        }
    }

    exit(0);
} catch (Exception $e) {
    if ($e->getPrevious()) {
        $message = (string)$e->getPrevious();
    } else {
        $message = $e->getMessage();
    }
    echo $message . PHP_EOL;
    exit(1);
}
