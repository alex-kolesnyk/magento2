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
 * @category    Enterprise
 * @package     Enterprise_PageCache
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Cart sidebar container
 */
class Enterprise_PageCache_Model_Container_Orders extends Enterprise_PageCache_Model_Container_Abstract
{
    const CACHE_TAG_PREFIX = 'orders';

    /**
     * Get cart hash from cookies
     */
    protected function _getIdentificator()
    {
        return (isset($_COOKIE[Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER])) ?
            $_COOKIE[Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER] : null;
    }

    /**
     * Get cache identifier
     */
    protected function _getCacheId()
    {

        return 'CONTAINER_ORDERS_'.md5($this->_placeholder->getAttribute('cache_id') . $this->_getIdentificator());
    }

    /**
     * Generate block content
     * @param $content
     */
    public function applyInApp(&$content)
    {
        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');
        $block = new $block;
        $block->setTemplate($template);
        $blockContent = $block->toHtml();
        $cacheId = $this->_getCacheId();
        if ($cacheId) {
            $cacheTag = md5(self::CACHE_TAG_PREFIX . $this->_getIdentificator());
            $this->_saveCache($blockContent, $cacheId, array($cacheTag));
        }
        $this->_applyToContent($content, $blockContent);
        return true;
    }
}
