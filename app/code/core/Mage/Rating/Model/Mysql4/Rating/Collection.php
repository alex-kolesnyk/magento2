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
 * @package    Mage_Rating
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Rating collection resource model
 *
 * @category   Mage
 * @package    Mage_Rating
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Rating_Model_Mysql4_Rating_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /*
    protected $_ratingTable;
    protected $_ratingEntityTable;
    protected $_ratingOptionTable;
    protected $_ratingVoteTable;
    */

    public function _construct()
    {
        $this->_init('rating/rating');
    }

    /**
     * add entity filter
     *
     * @param   int|string $entity
     * @return  Varien_Data_Collection_Db
     */
    public function addEntityFilter($entity)
    {
    	$this->_sqlSelect->join($this->getTable('rating_entity'),
    	   'main_table.entity_id='.$this->getTable('rating_entity').'.entity_id');

        if (is_numeric($entity)) {
            $this->addFilter('entity',
                $this->getConnection()->quoteInto($this->getTable('rating_entity').'.entity_id=?', $entity),
                'string');
        }
        elseif (is_string($entity)) {
            $this->addFilter('entity',
                $this->getConnection()->quoteInto($this->getTable('rating_entity').'.entity_code=?', $entity),
                'string');
        }
        return $this;
    }

    /**
     * set order by position field
     *
     * @param   string $dir
     * @return  Varien_Data_Collection_Db
     */
    public function setPositionOrder($dir='ASC')
    {
        $this->setOrder('main_table.position', $dir);
        return $this;
    }

    /**
     * add options to ratings in collection
     *
     * @return Varien_Data_Collection_Db
     */
    public function addOptionToItems()
    {
        $arrRatingId = $this->getColumnValues('rating_id');

        if (!empty($arrRatingId)) {
            $collection = Mage::getResourceModel('rating/rating_option_collection')
                ->addRatingFilter($arrRatingId)
                ->setPositionOrder()
                ->load();

            foreach ($this as $rating) {
            	$rating->setOptions($collection->getItemsByColumnValue('rating_id', $rating->getId()));
            }
        }

        return $this;
    }

    public function addEntitySummaryToItem($entityPkValue)
    {
        $arrRatingId = $this->getColumnValues('rating_id');

        if( count($arrRatingId) == 0 ) {
            return;
        }

        $sql = "SELECT
                    {$this->getTable('rating_vote')}.rating_id as rating_id,
                    SUM({$this->getTable('rating_vote')}.percent) as sum,
                    COUNT(*) as count
                FROM
                    {$this->getTable('rating_vote')}
                WHERE
                    {$this->getConnection()->quoteInto($this->getTable('rating_vote').'.rating_id IN (?)', $arrRatingId)}
                    AND {$this->getConnection()->quoteInto($this->getTable('rating_vote').'.entity_pk_value=?', $entityPkValue)}
                GROUP BY
                    {$this->getTable('rating_vote')}.rating_id";

        $data = $this->getConnection()->fetchAll($sql);

        foreach ($data as $item) {
        	$rating = $this->getItemById($item['rating_id']);
        	if ($rating && $item['count']>0) {
        	    $rating->setSummary($item['sum']/$item['count']);
        	}
        }
        return $this;
    }
}
