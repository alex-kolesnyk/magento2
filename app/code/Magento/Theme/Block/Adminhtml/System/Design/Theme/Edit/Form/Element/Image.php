<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Theme
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Image form element that generates correct thumbnail image URL for theme preview image
 */
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element;

class Image extends \Magento\Data\Form\Element\Image
{
    /**
     * @var \Magento\View\Design\Theme\Image\PathInterface
     */
    protected $_imagePath;

    /**
     * @param \Magento\Escaper $escaper
     * @param \Magento\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\UrlInterface $urlBuilder
     * @param \Magento\View\Design\Theme\Image\PathInterface $imagePath
     * @param array $attributes
     */
    public function __construct(
        \Magento\Escaper $escaper,
        \Magento\Data\Form\Element\Factory $factoryElement,
        \Magento\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\UrlInterface $urlBuilder,
        \Magento\View\Design\Theme\Image\PathInterface $imagePath,
        $attributes = array()
    ) {
        $this->_imagePath = $imagePath;
        parent::__construct($escaper, $factoryElement, $factoryCollection, $urlBuilder, $attributes);
    }

    /**
     * Get image preview url
     *
     * @return string
     */
    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $url = $this->_imagePath->getPreviewImageDirectoryUrl() . $this->getValue();
        }
        return $url;
    }
}
