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
 * @package    Mage_Directory
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Currency Mysql4 resourcre model
 *
 * @category   Mage
 * @package    Mage_Directory
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Directory_Model_Mysql4_Currency extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_currencyTable;
    protected $_currencyNameTable;
    protected $_currencyRateTable;
    protected $_countryCurrencyTable;
    
    protected static $_rateCache;
    
    protected function _construct()
    {
        $this->_init('directory/currency', 'currency_code');
    }

    public function __construct() 
    {
        $resource = Mage::getSingleton('core/resource');
        $this->_currencyTable       = $resource->getTableName('directory/currency');
        $this->_currencyNameTable   = $resource->getTableName('directory/currency_name');
        $this->_currencyRateTable   = $resource->getTableName('directory/currency_rate');
        $this->_countryCurrencyTable= $resource->getTableName('directory/country_currency');
        
        parent::__construct();
    }
    
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $read = $this->getConnection('read');
        if ($read && $object->getId()) {            
            $select = $read->select()
                ->from($this->_currencyTable)
                ->join($this->_currencyNameTable, "$this->_currencyNameTable.currency_code=$this->_currencyTable.currency_code")
                ->where($this->_currencyTable.'.currency_code=?', $object->getId())
                ->where($this->_currencyNameTable.'.language_code=?', $object->getLanguageCode());
            $data = $read->fetchRow($select);
            $object->addData($data);
        }
        return $this;
    }
    
    public function getRate($currencyFrom, $currencyTo)
    {
        if ($currencyFrom instanceof Mage_Directory_Model_Currency) {
            $currencyFrom = $currencyFrom->getCode();
        }
        
        if ($currencyTo instanceof Mage_Directory_Model_Currency) {
            $currencyTo = $currencyTo->getCode();
        }
        
        if ($currencyFrom == $currencyTo) {
            return 1;
        }
        
        if (!isset(self::$_rateCache[$currencyFrom][$currencyTo])) {
            $read = $this->getConnection('read');
            $select = $read->select()
                ->from($this->_currencyRateTable, 'rate')
                ->where('currency_from=?', strtoupper($currencyFrom))
                ->where('currency_to=?', strtoupper($currencyTo));
                
            self::$_rateCache[$currencyFrom][$currencyTo] = $read->fetchOne($select);
        }
        return self::$_rateCache[$currencyFrom][$currencyTo];
    }
    
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        parent::_afterSave($object);
        if ($rates = $object->getRates()) {
            $write = $this->getConnection('write');
            $table  = $write->quoteIdentifier($this->_currencyRateTable);
            $colFrom= $write->quoteIdentifier('currency_from');
            $colTo  = $write->quoteIdentifier('currency_to');
            $colRate= $write->quoteIdentifier('rate');
            
            $sql = 'REPLACE INTO ' . $table . ' (' . $colFrom . ', ' . $colTo . ', ' . $colRate . ') VALUES ';
            $values = array();
            foreach ($rates as $currencyCode => $rate) {
                $values[] = $write->quoteInto('(?)', array($object->getId(), $currencyCode, $rate));
            }
            $sql.= implode(',', $values);
            $write->query($sql);
        }
        return $this;
    }
}
