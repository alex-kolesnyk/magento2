<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Page Wysiwyg Images Helper
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 */
class Mage_Cms_Helper_Page_Wysiwyg_Images extends Mage_Core_Helper_Abstract
{

    /**
     * Current directory path
     * @var string
     */
    protected $_currentPath;

    /**
     * Current directory URL
     * @var string
     */
    protected $_currentUrl;

    /**
     * Images Storage root directory
     *
     * @return string
     */
    public function getStorageRoot()
    {
        $root = $this->correctPath( $this->getStorage()->getConfigData('upload_root') );
        return Mage::getConfig()->getOptions()->getBaseDir() . DS . $root;
    }

    /**
     * Images Storage base URL
     *
     * @return string
     */
    public function getBaseUrl()
    {
        $root = $this->correctPath( $this->getStorage()->getConfigData('upload_root') );
        $root = $this->convertPathToUrl($root);
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $root . '/';
    }

    /**
     * Ext Tree node key name
     *
     * @return string
     */
    public function getTreeNodeName()
    {
        return 'node';
    }

    /**
     * Encode path to HTML element id
     *
     * @param string $path Path to file/directory
     * @return string
     */
    public function convertPathToId($path)
    {
        return Mage::helper('core')->urlEncode($path);
    }

    /**
     * Decode HTML element id
     *
     * @param string $id
     * @return string
     */
    public function convertIdToPath($id)
    {
        return Mage::helper('core')->urlDecode($id);
    }

    /**
     * File system path correction
     *
     * @param string $path Original path
     * @param boolean $trim Trim slashes or not
     * @return string
     */
    public function correctPath($path, $trim = true)
    {
        $path = strtr($path, "\\\/", DS . DS);
        if ($trim) {
            $path = trim($path, DS);
        }
        return $path;
    }

    /**
     * Return file system path as Url string
     *
     * @param string $path
     * @return string
     */
    public function convertPathToUrl($path)
    {
        return str_replace(DS, '/', $path);
    }

    /**
     * Return path of the current selected directory or root directory for startup
     *
     * @return string
     */
    public function getCurrentPath()
    {
        if (!$this->_currentPath) {
            $currentPath = $this->getStorageRoot();
            $path = $this->_getRequest()->getParam($this->getTreeNodeName());
            if ($path) {
                $path = $this->convertIdToPath($path);
                $path = $this->correctPath($path);
                $path = $currentPath . DS . $path;
                if (is_dir($path)) {
                    $currentPath = $path;
                }
            }
            $this->_currentPath = $currentPath;
        }
        return $this->_currentPath;
    }

    /**
     * Return URL based on current selected directory or root directory for startup
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        if (!$this->_currentUrl) {
            $path = str_replace(Mage::getConfig()->getOptions()->getBaseDir(), '', $this->getCurrentPath());
            $path = trim($path, DS);
            $this->_currentUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)
                . $this->convertPathToUrl($path)
                . '/';
        }
        return $this->_currentUrl;
    }

    /**
     * Storage model singleton
     *
     * @return Mage_Cms_Model_Adminhtml_Page_Wysiwyg_Images_Storage
     */
    public function getStorage()
    {
        return Mage::getSingleton('cms/adminhtml_page_wysiwyg_images_storage');
    }
}
