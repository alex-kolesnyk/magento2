<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Theme Image Path
 */
namespace Magento\View\Design\Theme\Image;

class Path
{
    /**
     * Image preview path
     */
    const PREVIEW_DIRECTORY_PATH = 'theme/preview';

    /**
     * Default theme preview image
     */
    const DEFAULT_PREVIEW_IMAGE = 'Magento_Core::theme/default_preview.jpg';

    /**
     * @var \Magento\App\Dir
     */
    protected $dir;

    /**
     * @var \Magento\View\Url
     */
    protected $viewUrl;

    /**
     * @var \Magento\UrlInterface
     */
    protected $url;

    /**
     * Initialize dependencies
     *
     * @param \Magento\App\Dir $dir
     * @param \Magento\View\Url $viewUrl
     * @param \Magento\UrlInterface $url
     */
    public function __construct(
        \Magento\App\Dir $dir,
        \Magento\View\Url $viewUrl,
        \Magento\UrlInterface $url
    ) {
        $this->dir = $dir;
        $this->viewUrl = $viewUrl;
        $this->url = $url;
    }

    /**
     * Get preview image directory url
     *
     * @return string
     */
    public function getPreviewImageDirectoryUrl()
    {
        return $this->url->getBaseUrl(\Magento\Core\Model\Store::URL_TYPE_MEDIA)
            . self::PREVIEW_DIRECTORY_PATH . '/';
    }

    /**
     * Return default themes preview image url
     *
     * @return string
     */
    public function getPreviewImageDefaultUrl()
    {
        return $this->viewUrl->getViewFileUrl(self::DEFAULT_PREVIEW_IMAGE);
    }

    /**
     * Get directory path for preview image
     *
     * @return string
     */
    public function getImagePreviewDirectory()
    {
        return $this->dir->getDir(\Magento\App\Dir::MEDIA) . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, self::PREVIEW_DIRECTORY_PATH);
    }

    /**
     * Temporary directory path to store images
     *
     * @return string
     */
    public function getTemporaryDirectory()
    {
        return implode(DIRECTORY_SEPARATOR, array(
            $this->dir->getDir(\Magento\App\Dir::MEDIA), 'theme', 'origin'
        ));
    }
}
