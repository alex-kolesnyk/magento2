<?php
/**
 * Resources configuration filesystem loader
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\App\Resource\Config;

class Reader extends \Magento\Config\Reader\Filesystem
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_idAttributes = array(
        '/config/resource' => 'name'
    );

    /**
     * @param \Magento\Config\FileResolverInterface $fileResolver
     * @param \Magento\App\Resource\Config\Converter $converter
     * @param \Magento\App\Resource\Config\SchemaLocator $schemaLocator
     * @param \Magento\Config\ValidationStateInterface $validationState
     * @param string $fileName
     */
    public function __construct(
        \Magento\Config\FileResolverInterface $fileResolver,
        \Magento\App\Resource\Config\Converter $converter,
        \Magento\App\Resource\Config\SchemaLocator $schemaLocator,
        \Magento\Config\ValidationStateInterface $validationState,
        $fileName = 'resources.xml'
    ) {
        parent::__construct($fileResolver, $converter, $schemaLocator, $validationState, $fileName);
    }

    /**
     * Read resource configuration
     *
     * @param string $scope
     * @return array
     */
    public function read($scope = null)
    {
        return ($scope !== 'primary') ? parent::read($scope) : array();
    }
}
