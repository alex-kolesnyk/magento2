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
 * @package     Enterprise_GiftRegistry
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * GiftRegistry collection
 *
 * @category   Enterprise
 * @package    Enterprise_GiftRegistry
 */
class Enterprise_GiftRegistry_Model_Mysql4_Entity_Collection extends Enterprise_Enterprise_Model_Core_Mysql4_Collection_Abstract
{
    /**
     * Internal constructor
     */
    protected function _construct()
    {
        $this->_init('enterprise_giftregistry/entity', 'registry_id');
    }
    /**
     * Load collection where customer id equals passed parameter
     *
     * @param int $id
     * @return Enterprise_GiftRegistry_Model_Mysql4_Entity_Collection
     */
    public function loadByCustomerId($id)
    {
        $this->getSelect()->where('main_table.customer_id = ?', $id);
        return $this->load();
    }
    
}