<?php
/**
 * {license_notice}
 *
 * @category   Tools
 * @package    view
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Generator of rules which and where folders from code base should be copied
 */
namespace Magento\Tools\View\Generator;

use Magento\Filesystem\DirectoryList;

class CopyRule
{
    /**
     * @var \Magento\Filesystem
     */
    private $_filesystem;

    /**
     * @var \Magento\Core\Model\Theme\Collection
     */
    private $_themes;

    /**
     * @var \Magento\View\Design\Fallback\Rule\RuleInterface
     */
    private $_fallbackRule;

    /**
     * PCRE matching a named placeholder
     *
     * @var string
     */
    private $_placeholderPcre = '#%(.+?)%#';

    /**
     * Constructor
     *
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\Core\Model\Theme\Collection $themes
     * @param \Magento\View\Design\Fallback\Rule\RuleInterface $fallbackRule
     */
    public function __construct(
        \Magento\Filesystem $filesystem,
        \Magento\Core\Model\Theme\Collection $themes,
        \Magento\View\Design\Fallback\Rule\RuleInterface $fallbackRule
    ) {
        $this->_filesystem = $filesystem;
        $this->_themes = $themes;
        $this->_fallbackRule = $fallbackRule;
    }

    /**
     * Get rules for copying static view files
     * returns array(
     *      array('source' => <Absolute Source Path>, 'destinationContext' => <Destination Path Context>),
     *      ......
     * )
     *
     * @return array
     */
    public function getCopyRules()
    {
        $result = array();
        /** @var $theme \Magento\View\Design\ThemeInterface */
        foreach ($this->_themes as $theme) {
            $area = $theme->getArea();
            $nonModularLocations = $this->_fallbackRule->getPatternDirs(array(
                'area'      => $area,
                'theme'     => $theme,
            ));
            $modularLocations = $this->_fallbackRule->getPatternDirs(array(
                'area'      => $area,
                'theme'     => $theme,
                'namespace' => $this->_composePlaceholder('namespace'),
                'module'    => $this->_composePlaceholder('module'),
            ));
            $allDirPatterns = array_merge(
                array_reverse($modularLocations),
                array_reverse($nonModularLocations)
            );
            foreach ($allDirPatterns as $pattern) {
                foreach ($this->_getMatchingDirs($pattern) as $srcDir) {
                    $paramsFromDir = $this->_parsePlaceholders($srcDir, $pattern);
                    if (!empty($paramsFromDir['namespace']) && !empty($paramsFromDir['module'])) {
                        $module = $paramsFromDir['namespace'] . '_' . $paramsFromDir['module'];
                    } else {
                        $module = null;
                    }

                    $destinationContext = array(
                        'area' => $area,
                        'themePath' => $theme->getThemePath(),
                        'locale' => null, // Temporary locale is not taken into account
                        'module' => $module
                    );

                    $result[] = array(
                        'source' => $srcDir,
                        'destinationContext' => $destinationContext,
                    );
                }
            }
        }
        return $result;
    }

    /**
     * Compose a named placeholder that does not require escaping when directly used in a PCRE
     *
     * @param string $name
     * @return string
     */
    private function _composePlaceholder($name)
    {
        return '%' . $name . '%';
    }

    /**
     * Retrieve absolute directory paths matching a pattern with placeholders
     *
     * @param string $dirPattern
     * @return array
     */
    private function _getMatchingDirs($dirPattern)
    {
        $pattern = preg_replace_callback('/[\\\\^$.[\\]|()?*+{}\\-\\/]/', function($matches) {
            switch ($matches[0]) {
                case '*':
                    return '.*';
                case '?':
                    return '.';
                default:
                    return '\\'.$matches[0];
            }
        }, $dirPattern, -1, $count);
        $directoryHandler = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT);
        if ($count) {
            // autodetect pattern base directory because the filesystem interface requires it
            $firstPlaceholderPos = strpos($pattern, '.*');
            $patternBaseDir = substr($pattern, 0, $firstPlaceholderPos);
            $patternTrailing = substr($pattern, $firstPlaceholderPos);

            $paths = $directoryHandler->search('#' . $patternTrailing . '#', $patternBaseDir);
        } else {
            // pattern is already a valid path containing no placeholders
            $paths = array($dirPattern);
        }
        $result = array();
        foreach ($paths as $path) {
            if ($directoryHandler->isDirectory($path)) {
                $result[] = $directoryHandler->getAbsolutePath($path);
            }
        }
        return $result;
    }

    /**
     * Retrieve placeholder values
     *
     * @param string $subject
     * @param string $pattern
     * @return array
     */
    private function _parsePlaceholders($subject, $pattern)
    {
        $pattern = preg_quote($pattern, '#');
        $parserPcre = '#^' . preg_replace($this->_placeholderPcre, '(?P<\\1>.+?)', $pattern) . '$#';
        if (preg_match($parserPcre, $subject, $placeholders)) {
            return $placeholders;
        }
        return array();
    }
}
