<?php
/**
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */

namespace Magento\Tools\I18n\Code;

/**
 *  Files collector
 */
class FilesCollector
{
    /**
     * Get files
     *
     * @param array $paths
     * @param bool $fileMask
     * @return array
     */
    public function getFiles(array $paths, $fileMask = false)
    {
        $files = array();
        foreach ($paths as $path) {
            foreach ($this->_getIterator($path, $fileMask) as $file) {
                $files[] = (string)$file;
            }
        }
        sort($files);
        return $files;
    }

    /**
     * Get files iterator
     *
     * @param string $path
     * @param bool $fileMask
     * @return \RecursiveIteratorIterator|\RegexIterator
     * @throws \InvalidArgumentException
     */
    protected function _getIterator($path, $fileMask = false)
    {
        try {
            $directoryIterator = new \RecursiveDirectoryIterator($path,
                \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS);
            $iterator = new \RecursiveIteratorIterator($directoryIterator);
        } catch (\UnexpectedValueException $valueException) {
            throw new \InvalidArgumentException(sprintf('Cannot read directory for parse phrase: "%s".', $path));
        }
        if ($fileMask) {
            $iterator = new \RegexIterator($iterator, $fileMask);
        }
        return $iterator;
    }
}
