<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Tag
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tag collection model
 *
 * @category   Mage
 * @package    Mage_Tag
 * @author      Michael Bessolov <michael@varien.com>
 */

class Mage_Tag_Model_Mysql4_Tag_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_tagRelTable;

    protected function _construct()
    {
        $this->_init('tag/tag');
        $this->_tagRelTable = $this->getTable('tag/relation');
    }

    public function load($printQuery = false, $logQuery = false)
    {
        return parent::load($printQuery, $logQuery);
    }

    public function addPopularity($limit=null)
    {
        $this->getSelect()
            ->joinLeft($this->_tagRelTable, 'main_table.tag_id='.$this->_tagRelTable.'.tag_id', array('tag_relation_id', 'popularity' => 'COUNT(DISTINCT '.$this->_tagRelTable.'.tag_relation_id)'))
            ->group('main_table.tag_id');
        if (! is_null($limit)) {
            $this->getSelect()->limit($limit);
        }
        return $this;
    }

    public function addFieldToFilter($field, $condition)
    {
        if ('popularity' == $field) {
            // TOFIX
            $this->_sqlSelect->having($this->_getConditionSql('count(' . $this->_tagRelTable . '.tag_relation_id)', $condition));
        } else {
            parent::addFieldToFilter($field, $condition);
        }
        return $this;
    }

    /**
     * Get sql for get record count
     *
     * @return  string
     */

    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $countSelect = clone $this->_sqlSelect;
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::GROUP);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);

        $sql = $countSelect->__toString();
        // TOFIX
        $sql = preg_replace('/^select\s+.+?\s+from\s+/is', 'select COUNT(DISTINCT main_table.tag_id) from ', $sql);
        return $sql;
    }

    public function addStoreFilter($storeId)
    {
        $this->addFieldToFilter('main_table.store_id', $storeId);
        return $this;
    }

    public function addStatusFilter($status)
    {
        $this->addFieldToFilter('main_table.status', $status);
        return $this;
    }

    public function addProductFilter($productId)
    {
        $this->addFieldToFilter("{$this->_tagRelTable}.product_id", $productId);
        return $this;
    }

    public function addCustomerFilter($customerId)
    {
        $this->getSelect()
            ->where("{$this->_tagRelTable}.customer_id = ?", $customerId);
        return $this;
    }

    public function joinRel()
    {
        $this->getSelect()->joinLeft($this->_tagRelTable, 'main_table.tag_id='.$this->_tagRelTable.'.tag_id');
        return $this;
    }
}