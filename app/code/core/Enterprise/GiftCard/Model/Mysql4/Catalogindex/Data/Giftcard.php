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
 * @package    Enterprise_GiftCard
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://www.magentocommerce.com/license/enterprise-edition
 */

class Enterprise_GiftCard_Model_Mysql4_Catalogindex_Data_Giftcard extends Mage_CatalogIndex_Model_Mysql4_Data_Abstract
{
    protected $_cache = array();
    public function getAmounts($product, $store)
    {
        $isGlobal = ($store->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE) == Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL);

        if ($isGlobal) {
            $key = $product;
        } else {
            $website = $store->getWebsiteId();
            $key = "{$product}|{$website}";
        }

        if (!isset($this->_cache[$key])) {
            $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('enterprise_giftcard/amount'), array('value'))
                ->where('entity_id=?', $product);

            if ($isGlobal) {
                $select->where('website_id=?', 0);
            } else {
                $select->where('website_id IN (?)', array(0, $website));
            }
            $this->_cache[$key] = $this->_getReadAdapter()->fetchAll($select);
        }
        return $this->_cache[$key];
    }
}