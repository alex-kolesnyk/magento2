<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Directoty tree renderer for Cms Wysiwyg Images
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Magento_Adminhtml_Block_Cms_Wysiwyg_Images_Tree extends Magento_Adminhtml_Block_Template
{

    /**
     * Cms wysiwyg images
     *
     * @var Magento_Cms_Helper_Wysiwyg_Images
     */
    protected $_cmsWysiwygImages = null;

    /**
     * @param Magento_Cms_Helper_Wysiwyg_Images $cmsWysiwygImages
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Backend_Block_Template_Context $context
     * @param array $data
     */
    public function __construct(
        Magento_Cms_Helper_Wysiwyg_Images $cmsWysiwygImages,
        Magento_Core_Helper_Data $coreData,
        Magento_Backend_Block_Template_Context $context,
        array $data = array()
    ) {
        $this->_cmsWysiwygImages = $cmsWysiwygImages;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Json tree builder
     *
     * @return string
     */
    public function getTreeJson()
    {
        $storageRoot = $this->_cmsWysiwygImages->getStorageRoot();
        $collection = Mage::registry('storage')->getDirsCollection($this->_cmsWysiwygImages->getCurrentPath());
        $jsonArray = array();
        foreach ($collection as $item) {
            $jsonArray[] = array(
                'text'  => $this->_cmsWysiwygImages->getShortFilename($item->getBasename(), 20),
                'id'    => $this->_cmsWysiwygImages->convertPathToId($item->getFilename()),
                'path' => substr($item->getFilename(), strlen($storageRoot)),
                'cls'   => 'folder'
            );
        }
        return Zend_Json::encode($jsonArray);
    }

    /**
     * Json source URL
     *
     * @return string
     */
    public function getTreeLoaderUrl()
    {
        return $this->getUrl('*/*/treeJson');
    }

    /**
     * Root node name of tree
     *
     * @return string
     */
    public function getRootNodeName()
    {
        return __('Storage Root');
    }

    /**
     * Return tree node full path based on current path
     *
     * @return string
     */
    public function getTreeCurrentPath()
    {
        $treePath = array('root');
        if ($path = Mage::registry('storage')->getSession()->getCurrentPath()) {
            $path = str_replace($this->_cmsWysiwygImages->getStorageRoot(), '', $path);
            $relative = array();
            foreach (explode(DIRECTORY_SEPARATOR, $path) as $dirName) {
                if ($dirName) {
                    $relative[] =  $dirName;
                    $treePath[] =  $this->_cmsWysiwygImages->idEncode(implode(DIRECTORY_SEPARATOR, $relative));
                }
            }
        }
        return $treePath;
    }

    /**
     * Get tree widget options
     * @return array
     */
    public function getTreeWidgetOptions()
    {
        return array(
            "folderTree" => array(
                "rootName" => $this->getRootNodeName(),
                "url" => $this->getTreeLoaderUrl(),
                "currentPath"=> array_reverse($this->getTreeCurrentPath()),
            )
        );
    }
}
