<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Theme
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Theme\Block\Adminhtml\Wysiwyg\Files\Content;

/**
 * Files uploader block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Uploader extends \Magento\Backend\Block\Media\Uploader
{
    /**
     * Path to uploader template
     *
     * @var string
     */
    protected $_template = 'browser/content/uploader.phtml';

    /**
     * @var \Magento\Theme\Helper\Storage
     */
    protected $_storageHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\File\Size $fileSize
     * @param \Magento\Theme\Helper\Storage $storageHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\File\Size $fileSize,
        \Magento\Theme\Helper\Storage $storageHelper,
        array $data = array()
    ) {
        $this->_storageHelper = $storageHelper;
        parent::__construct($context, $fileSize, $data);
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Backend\Block\Media\Uploader
     */
    protected function _prepareLayout()
    {
        $this->getConfig()->setUrl(
            $this->getUrl('adminhtml/*/upload', $this->_storageHelper->getRequestParams())
        );
        return parent::_prepareLayout();
    }

    /**
     * Return storage helper
     *
     * @return \Magento\Theme\Helper\Storage
     */
    public function getHelperStorage()
    {
        return $this->_storageHelper;
    }
}
