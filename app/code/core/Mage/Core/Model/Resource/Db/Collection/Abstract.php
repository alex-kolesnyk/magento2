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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2010 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Abstract Core Resource Collection
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Core_Model_Resource_Db_Collection_Abstract extends Varien_Data_Collection_Db
{
    const CACHE_TAG = 'COLLECTION_DATA';

    /**
     * Model name
     *
     * @var string
     */
    protected $_model;

    /**
     * Resource model name
     *
     * @var string
     */
    protected $_resourceModel;

    /**
     * Resource instance
     *
     * @var Mage_Core_Model_Db_Abstract
     */
    protected $_resource;

    /**
     * Store joined tables here
     *
     * @var array
     */
    protected $_joinedTables = array();

    /**
     * Collection constructor
     *
     * @param Mage_Core_Model_Db_Abstract $resource
     */
    public function __construct($resource = null)
    {
        parent::__construct();
        $this->_construct();
        $this->_resource = $resource;
        $this->setConnection($this->getResource()->getReadConnection());
        $this->_initSelect();
    }

    /**
     * Initialization here
     *
     */
    protected function _construct()
    {}

    /**
     * Initialize select object
     *
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    protected function _initSelect()
    {
        $this->getSelect()
            ->from(array('main_table' => $this->getResource()->getMainTable()));
        return $this;
    }

    /**
     * Standard resource collection initalization
     *
     * @param string $model
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    protected function _init($model, $resourceModel=null)
    {
        $this->setModel($model);
        if (is_null($resourceModel)) {
            $resourceModel = $model;
        }
        $this->setResourceModel($resourceModel);
        return $this;
    }

    /**
     * Set model name for collection items
     *
     * @param string $model
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    public function setModel($model)
    {
        if (is_string($model)) {
            $this->_model = $model;
            $this->setItemObjectClass(Mage::getConfig()->getModelClassName($model));
        }
        return $this;
    }

    /**
     * Get model instance
     *
     * @param array $args
     * @return Varien_Object
     */
    public function getModelName($args=array())
    {
        return $this->_model;
    }

    /**
     * Set resource model name
     *
     * @param string $model
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    public function setResourceModel($model)
    {
        $this->_resourceModel = $model;
        return $this;
    }

    /**
     * Retrieve resource model name
     *
     * @return string
     */
    public function getResourceModelName()
    {
        return $this->_resourceModel;
    }

    /**
     * Get resource instance
     *
     * @return Mage_Core_Model_Resouce_Db_Abstract
     */
    public function getResource()
    {
        if (empty($this->_resource)) {
            $this->_resource = Mage::getResourceModel($this->getResourceModelName());
        }
        return $this->_resource;
    }

    /**
     * Retrieve table name
     *
     * @param string $table
     * @return string
     */
    public function getTable($table)
    {
        return $this->getResource()->getTable($table);
    }

    /**
     * Retrieve all ids for collection
     *
     * @return array
     */
    public function getAllIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');
        return $this->getConnection()->fetchCol($idsSelect);
    }

    /**
     * Join table to collection select
     *
     * @param string $table
     * @param strign $cond
     * @param string $cols
     * @return Mage_Core_Model_Resouce_Db_Collection_Abstract
     */
    public function join($table, $cond, $cols = '*')
    {
        if (is_array($table)) {
            foreach ($table as $k => $v) {
                $alias = $k;
                $table = $v;
                break;
            }
        } else {
            $alias = $table;
        }

        if (!isset($this->_joinedTables[$alias])) {
            $this->getSelect()->join(
                array($alias => $this->getTable($table)),
                $cond,
                $cols
            );
            $this->_joinedTables[$table] = true;
        }
        return $this;
    }

    /**
     * Load data
     *
     * @return  Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            Mage::dispatchEvent('core_collection_abstract_load_before', array('collection' => $this));
        }
        parent::load($printQuery, $logQuery);
        return $this;
    }

    /**
     * Redeclare after load method for specifying collection items original data
     *
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this->_items as $item) {
            $item->setOrigData();
        }
        Mage::dispatchEvent('core_collection_abstract_load_after', array('collection' => $this));
        return $this;
    }

    /**
     * Save all the entities in the collection
     *
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    public function save()
    {
        foreach ($this->getItems() as $item) {
            $item->save();
        }
        return $this;
    }

    /**
     * Check if cache can be used for collection
     *
     * @return bool
     */
    protected function _canUseCache()
    {
        return Mage::app()->useCache('collections') && !empty($this->_cacheConf);
    }

    /**
     * Load cached data for select
     *
     * @param Zend_Db_Select $select
     * @return string | false
     */
    protected function _loadCache($select)
    {
        $data = Mage::app()->loadCache($this->_getSelectCacheId($select));
        return $data;
    }

    /**
     * Save collection data to cache
     *
     * @param array $data
     * @param Zend_Db_Select $select
     * @return unknown_type
     */
    protected function _saveCache($data, $select)
    {
        Mage::app()->saveCache(serialize($data), $this->_getSelectCacheId($select), $this->_getCacheTags());
        return $this;
    }

    /**
     * Redeclared for processing cache tags throw application object
     *
     * @return array
     */
    protected function _getCacheTags()
    {
        $tags = parent::_getCacheTags();
        $tags[] = Mage_Core_Model_App::CACHE_TAG;
        $tags[] = self::CACHE_TAG;
        return $tags;
    }

    /**
     * Format Date to internal database date format
     *
     * @param int|string|Zend_Date $date
     * @param boolean $includeTime
     * @return string
     */
    public function formatDate($date, $includeTime = true)
    {
        return Varien_Date::formatDate($date, $includeTime);
    }
}
