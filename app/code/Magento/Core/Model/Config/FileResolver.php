<?php
/**
 * Application config file resolver
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Core\Model\Config;

class FileResolver implements \Magento\Config\FileResolverInterface
{
    /**
     * Module configuration file reader
     *
     * @var \Magento\Module\Dir\Reader
     */
    protected $_moduleReader;

    /**
     * @var \Magento\Config\FileIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @param \Magento\Module\Dir\Reader $moduleReader
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\Config\FileIteratorFactory $iteratorFactory
     */
    public function __construct(
        \Magento\Module\Dir\Reader $moduleReader,
        \Magento\Filesystem $filesystem,
        \Magento\Config\FileIteratorFactory $iteratorFactory
    ) {
        $this->iteratorFactory = $iteratorFactory;
        $this->filesystem = $filesystem;
        $this->_moduleReader = $moduleReader;
    }

    /**
     * @inheritdoc
     */
    public function get($filename, $scope)
    {
        switch ($scope) {
            case 'primary':
                $directory = $this->filesystem->getDirectoryRead(\Magento\Filesystem::CONFIG);
                $fileList = $directory->search('#' . preg_quote($filename) . '$#');
                break;
            case 'global':
                $directory = $this->filesystem->getDirectoryRead(\Magento\Filesystem::APP);
                $fileList = $this->_moduleReader->getConfigurationFiles($filename);
                break;
            default:
                $directory = $this->filesystem->getDirectoryRead(\Magento\Filesystem::APP);
                $fileList = $this->_moduleReader->getConfigurationFiles($scope . '/' . $filename);
                break;
        }
        $output = array();
        foreach ($fileList as $file) {
            $output[] = $directory->getRelativePath($file);
        }
//        absolute pathes here
        return $this->iteratorFactory->create(
            $directory,
            $output
        );
    }
}
