<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Reports
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Abstract report aggregate resource model
 *
 * @category    Magento
 * @package     Magento_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Report;

abstract class AbstractReport extends \Magento\Core\Model\Resource\Db\AbstractDb
{
    /**
     * Flag object
     *
     * @var \Magento\Reports\Model\Flag
     */
    protected $_flag     = null;

    /**
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * @var \Magento\Reports\Model\FlagFactory
     */
    protected $_reportsFlagFactory;

    /**
     * @param \Magento\Logger $logger
     * @param \Magento\Core\Model\Resource $resource
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Reports\Model\FlagFactory $reportsFlagFactory
     */
    public function __construct(
        \Magento\Logger $logger,
        \Magento\Core\Model\Resource $resource,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Reports\Model\FlagFactory $reportsFlagFactory
    ) {
        parent::__construct($resource);
        $this->_logger = $logger;
        $this->_locale = $locale;
        $this->_reportsFlagFactory = $reportsFlagFactory;
    }

    /**
     * Retrieve flag object
     *
     * @return \Magento\Reports\Model\Flag
     */
    protected function _getFlag()
    {
        if ($this->_flag === null) {
            $this->_flag = $this->_reportsFlagFactory->create();
        }
        return $this->_flag;
    }

    /**
     * Saves flag
     *
     * @param string $code
     * @param mixed $value
     * @return \Magento\Reports\Model\Resource\Report\AbstractReport
     */
    protected function _setFlagData($code, $value = null)
    {
        $this->_getFlag()
            ->setReportFlagCode($code)
            ->unsetData()
            ->loadSelf();

        if ($value !== null) {
            $this->_getFlag()->setFlagData($value);
        }

        $time = \Magento\Date::toTimestamp(true);
        // touch last_update
        $this->_getFlag()->setLastUpdate($this->formatDate($time));

        $this->_getFlag()->save();

        return $this;
    }

    /**
     * Retrieve flag data
     *
     * @param string $code
     * @return mixed
     */
    protected function _getFlagData($code)
    {
        $this->_getFlag()
            ->setReportFlagCode($code)
            ->unsetData()
            ->loadSelf();

        return $this->_getFlag()->getFlagData();
    }

    /**
     * Truncate table
     *
     * @param string $table
     * @return \Magento\Reports\Model\Resource\Report\AbstractReport
     */
    protected function _truncateTable($table)
    {
        if ($this->_getWriteAdapter()->getTransactionLevel() > 0) {
            $this->_getWriteAdapter()->delete($table);
        } else {
            $this->_getWriteAdapter()->truncateTable($table);
        }
        return $this;
    }

    /**
     * Clear report table by specified date range.
     * If specified source table parameters,
     * condition will be generated by source table subselect.
     *
     * @param string $table
     * @param string|null $from
     * @param string|null $to
     * @param \Zend_Db_Select|string|null $subSelect
     * @param bool $doNotUseTruncate
     * @return \Magento\Reports\Model\Resource\Report\AbstractReport
     */
    protected function _clearTableByDateRange($table, $from = null, $to = null, $subSelect = null,
        $doNotUseTruncate = false
    ) {
        if ($from === null && $to === null && !$doNotUseTruncate) {
            $this->_truncateTable($table);
            return $this;
        }

        if ($subSelect !== null) {
            $deleteCondition = $this->_makeConditionFromDateRangeSelect($subSelect, 'period');
        } else {
            $condition = array();
            if ($from !== null) {
                $condition[] = $this->_getWriteAdapter()->quoteInto('period >= ?', $from);
            }

            if ($to !== null) {
                $condition[] = $this->_getWriteAdapter()->quoteInto('period <= ?', $to);
            }
            $deleteCondition = implode(' AND ', $condition);
        }
        $this->_getWriteAdapter()->delete($table, $deleteCondition);
        return $this;
    }

    /**
     * Generate table date range select
     *
     * @param string $table
     * @param string $column
     * @param string $whereColumn
     * @param string|null $from
     * @param string|null $to
     * @param array $additionalWhere
     * @param string $alias
     * @return \Magento\DB\Select
     */
    protected function _getTableDateRangeSelect($table, $column, $whereColumn, $from = null, $to = null,
        $additionalWhere = array(), $alias = 'date_range_table'
    ) {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from(
                array($alias => $table),
                $adapter->getDatePartSql(
                    $this->getStoreTZOffsetQuery(array($alias => $table), $alias . '.' . $column, $from, $to)
                )
            )
            ->distinct(true);

        if ($from !== null) {
            $select->where($alias . '.' . $whereColumn . ' >= ?', $from);
        }

        if ($to !== null) {
            $select->where($alias . '.' . $whereColumn . ' <= ?', $to);
        }

        if (!empty($additionalWhere)) {
            foreach ($additionalWhere as $condition) {
                if (is_array($condition) && count($condition) == 2) {
                    $condition = $adapter->quoteInto($condition[0], $condition[1]);
                } elseif (is_array($condition)) { // Invalid condition
                    continue;
                }
                $condition = str_replace('{{table}}', $adapter->quoteIdentifier($alias), $condition);
                $select->where($condition);
            }
        }

        return $select;
    }

    /**
     * Make condition for using in where section
     * from select statement with single date column
     *
     * @result string|false
     *
     * @param \Magento\DB\Select $select
     * @param string $periodColumn
     * @return string
     */
    protected function _makeConditionFromDateRangeSelect($select, $periodColumn)
    {
        static $selectResultCache = array();
        $cacheKey = (string)$select;

        if (!array_key_exists($cacheKey, $selectResultCache)) {
            try {
                $selectResult = array();
                $query = $this->_getReadAdapter()->query($select);
                while (true == ($date = $query->fetchColumn())) {
                    $selectResult[] = $date;
                }
            } catch (\Exception $e) {
                $selectResult = false;
            }
            $selectResultCache[$cacheKey] = $selectResult;
        } else {
            $selectResult = $selectResultCache[$cacheKey];
        }
        if ($selectResult === false) {
            return false;
        }

        $whereCondition = array();
        $adapter = $this->_getReadAdapter();
        foreach ($selectResult as $date) {
            $whereCondition[] = $adapter->prepareSqlCondition($periodColumn, array('like' => $date));
        }
        $whereCondition = implode(' OR ', $whereCondition);
        if ($whereCondition == '') {
            $whereCondition = '1=0';  // FALSE condition!
        }

        return $whereCondition;
    }

    /**
     * Generate table date range select
     *
     * @param string $table
     * @param string $relatedTable
     * @param array $joinCondition
     * @param string $column
     * @param string $whereColumn
     * @param string|null $from
     * @param string|null $to
     * @param array $additionalWhere
     * @param string $alias
     * @param string $relatedAlias
     * @return \Magento\DB\Select
     */
    protected function _getTableDateRangeRelatedSelect($table, $relatedTable, $joinCondition, $column, $whereColumn,
        $from = null, $to = null, $additionalWhere = array(), $alias = 'date_range_table',
        $relatedAlias = 'related_date_range_table'
    ) {
        $adapter = $this->_getReadAdapter();
        $joinConditionSql = array();

        foreach ($joinCondition as $fkField => $pkField) {
            $joinConditionSql[] = sprintf('%s.%s = %s.%s', $alias, $fkField, $relatedAlias, $pkField);
        }

        $select = $adapter->select()
            ->from(
                array($alias => $table),
                $adapter->getDatePartSql(
                    $adapter->quoteIdentifier($alias . '.' . $column)
                )
            )
            ->joinInner(
                array($relatedAlias => $relatedTable),
                implode(' AND ', $joinConditionSql),
                array()
            )
            ->distinct(true);

        if ($from !== null) {
            $select->where($relatedAlias . '.' . $whereColumn . ' >= ?', $from);
        }

        if ($to !== null) {
            $select->where($relatedAlias . '.' . $whereColumn . ' <= ?', $to);
        }

        if (!empty($additionalWhere)) {
            foreach ($additionalWhere as $condition) {
                if (is_array($condition) && count($condition) == 2) {
                    $condition = $adapter->quoteInto($condition[0], $condition[1]);
                } elseif (is_array($condition)) { // Invalid condition
                    continue;
                }
                $condition = str_replace(
                    array('{{table}}', '{{related_table}}'),
                    array(
                        $adapter->quoteIdentifier($alias),
                        $adapter->quoteIdentifier($relatedAlias)
                    ),
                    $condition
                );
                $select->where($condition);
            }
        }

        return $select;
    }

    /**
     * Check range dates and transforms it to strings
     *
     * @param mixed $dateFrom
     * @param mixed $dateTo
     * @return \Magento\Reports\Model\Resource\Report\AbstractReport
     */
    protected function _checkDates(&$dateFrom, &$dateTo)
    {
        if ($dateFrom !== null) {
            $dateFrom = $this->formatDate($dateFrom);
        }

        if ($dateTo !== null) {
            $dateTo = $this->formatDate($dateTo);
        }

        return $this;
    }

    /**
     * Retrieve query for attribute with timezone conversion
     *
     * @param string|array $table
     * @param string $column
     * @param mixed $from
     * @param mixed $to
     * @param int|string|\Magento\Core\Model\Store|null $store
     * @return string
     */
    public function getStoreTZOffsetQuery($table, $column, $from = null, $to = null, $store = null)
    {
        $column = $this->_getWriteAdapter()->quoteIdentifier($column);

        if (null === $from) {
            $selectOldest = $this->_getWriteAdapter()->select()
                ->from($table, array("MIN($column)"));
            $from = $this->_getWriteAdapter()->fetchOne($selectOldest);
        }

        $periods = $this->_getTZOffsetTransitions(
            $this->_locale->storeDate($store)->toString(\Zend_Date::TIMEZONE_NAME), $from, $to
        );
        if (empty($periods)) {
            return $column;
        }

        $query = "";
        $periodsCount = count($periods);

        $i = 0;
        foreach ($periods as $offset => $timestamps) {
            $subParts = array();
            foreach ($timestamps as $ts) {
                $subParts[] = "($column between {$ts['from']} and {$ts['to']})";
            }

            $then = $this->_getWriteAdapter()
                ->getDateAddSql($column, $offset, \Magento\DB\Adapter\AdapterInterface::INTERVAL_SECOND);

            $query .= (++$i == $periodsCount) ? $then : "CASE WHEN " . join(" OR ", $subParts) . " THEN $then ELSE ";
        }

        return $query . str_repeat('END ', count($periods) - 1);
    }

    /**
     * Retrieve transitions for offsets of given timezone
     *
     * @param string $timezone
     * @param mixed $from
     * @param mixed $to
     * @return array
     */
    protected function _getTZOffsetTransitions($timezone, $from = null, $to = null)
    {
        $tzTransitions = array();
        try {
            if (!empty($from)) {
                $from = new \Zend_Date($from, \Magento\Date::DATETIME_INTERNAL_FORMAT);
                $from = $from->getTimestamp();
            }

            $to = new \Zend_Date($to, \Magento\Date::DATETIME_INTERNAL_FORMAT);
            $nextPeriod = $this->_getWriteAdapter()->formatDate($to->toString(\Magento\Date::DATETIME_INTERNAL_FORMAT));
            $to = $to->getTimestamp();

            $dtz = new \DateTimeZone($timezone);
            $transitions = $dtz->getTransitions();
            $dateTimeObject = new \Zend_Date('c');

            for ($i = count($transitions) - 1; $i >= 0; $i--) {
                $tr = $transitions[$i];
                if ($tr['ts'] > $to) {
                    continue;
                }

                $dateTimeObject->set($tr['time']);
                $tr['time'] = $this->_getWriteAdapter()
                    ->formatDate($dateTimeObject->toString(\Magento\Date::DATETIME_INTERNAL_FORMAT));
                $tzTransitions[$tr['offset']][] = array('from' => $tr['time'], 'to' => $nextPeriod);

                if (!empty($from) && $tr['ts'] < $from) {
                    break;
                }
                $nextPeriod = $tr['time'];
            }
        } catch (\Exception $e) {
            $this->_logger->logException($e);
        }

        return $tzTransitions;
    }


    /**
     * Retrieve store timezone offset from UTC in the form acceptable by SQL's CONVERT_TZ()
     *
     * @param mixed $store
     * @return string
     */
    protected function _getStoreTimezoneUtcOffset($store = null)
    {
        return $this->_locale->storeDate($store)->toString(\Zend_Date::GMT_DIFF_SEP);
    }

    /**
     * Retrieve date in UTC timezone
     *
     * @param mixed $date
     * @return \Zend_Date|null
     */
    protected function _dateToUtc($date)
    {
        if ($date === null) {
            return null;
        }
        $dateUtc = new \Zend_Date($date);
        $dateUtc->setTimezone('Etc/UTC');
        return $dateUtc;
    }
}
