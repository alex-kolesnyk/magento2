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
 * @package    Mage_Tax
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tax rate resource
 *
 * @category   Mage
 * @package    Mage_Tax
 * @author      Alexander Stadnitski <alexander@varien.com>
 */

class Mage_Tax_Model_Mysql4_Rate
{

    /**
     * resource tables
     */
    protected $_rateTable;

    protected $_rateDataTable;

    /**
     * resources
     */
    protected $_write;

    protected $_read;


    public function __construct()
    {
        $this->_rateTable = Mage::getSingleton('core/resource')->getTableName('tax/tax_rate');
        $this->_rateDataTable = Mage::getSingleton('core/resource')->getTableName('tax/tax_rate_data');

        $this->_read = Mage::getSingleton('core/resource')->getConnection('tax_read');
        $this->_write = Mage::getSingleton('core/resource')->getConnection('tax_write');
    }

    public function getIdFieldName()
    {
        return 'tax_rate_id';
    }

    public function load($model, $rateId)
    {
        $model->setData(array());
    }

    public function loadWithAttributes($rateId)
    {
        if( intval($rateId) <= 0 ) {
            return;
        }

        $select = $this->_read->select();
        $select->from($this->_rateTable);
        $select->where("{$this->_rateTable}.tax_rate_id = ?", $rateId);

        $rateTypes = Mage::getResourceModel('tax/rate_type_collection')->load()->getItems();

        $index = 0;
        foreach( $rateTypes as $type ) {
            $tableAlias = "trd_{$index}";
            $select->joinLeft(array($tableAlias => $this->_rateDataTable), "{$this->_rateTable}.tax_rate_id = {$tableAlias}.tax_rate_id AND {$tableAlias}.rate_type_id = '{$type->getTypeId()}'", array("rate_value{$type->getTypeId()}" => 'rate_value'));
            $index++;
        }

        $rateData = $this->_read->fetchRow($select);
        return $rateData;
    }

    public function save($rateObject)
    {
        $rateArray = array(
            'tax_county_id' => $rateObject->getTaxCountyId(),
            'tax_region_id' => $rateObject->getTaxRegionId(),
        );
        if ($rateObject->getTaxPostcode()) {
        	$rateArray['tax_postcode'] = $rateObject->getTaxPostcode();
        } else {
            $rateArray['tax_postcode'] = new Zend_Db_Expr('NULL');
        }
        if( intval($rateObject->getTaxRateId()) <= 0 ) {
            $this->_write->insert($this->_rateTable, $rateArray);
            $rateId = $this->_write->lastInsertId();
        } else {
            $rateId = $rateObject->getTaxRateId();
            $condition = $this->_write->quoteInto("{$this->_rateTable}.tax_rate_id=?", $rateId);
            $this->_write->update($this->_rateTable, $rateArray, $condition);

            $condition = $this->_write->quoteInto("{$this->_rateDataTable}.tax_rate_id=?", $rateId);
            $this->_write->delete($this->_rateDataTable, $condition);
        }

        foreach ($rateObject->getRateData() as $rateType => $rateValue) {
            $rateValueArray = array(
                'tax_rate_id' => $rateId,
                'rate_value' => $rateValue,
                'rate_type_id' => $rateType
            );
            $this->_write->insert($this->_rateDataTable, $rateValueArray);
        }
    }

    public function delete($rateObject)
    {
        $condition = $this->_write->quoteInto("{$this->_rateTable}.tax_rate_id=?", $rateObject->getTaxRateId());
        $this->_write->delete($this->_rateTable, $condition);
    }

    public function deleteAllRates()
    {
    	$this->_write->delete($this->_rateTable);
    }
}