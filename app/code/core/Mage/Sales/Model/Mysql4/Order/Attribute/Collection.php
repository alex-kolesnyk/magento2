<?php

class Mage_Sales_Model_Mysql4_Quote_Attribute_Collection extends Varien_Data_Collection_Db 
{
    static protected $_attributeTable = null;

    public function __construct() 
    {
        parent::__construct(Mage::registry('resources')->getConnection('sales_read'));
        self::$_attributeTable = Mage::registry('resources')->getTableName('sales', 'quote_attribute');
        $this->_sqlSelect->from(self::$_attributeTable);
        $this->setItemObjectClass(Mage::getConfig()->getModelClassName('sales', 'quote_attribute'));
    }
    
    public function loadByQuoteId($quoteId)
    {
        $this->addFilter('quote_id', (int)$quoteId, 'and');
        $this->load();
        return $this;
    }
    
    public function loadByEntity($entityType, $entityId=null)
    {
        $this->addFilter('entity_type', $entityType, 'and');
        if (!is_null($entityId)) {
            $this->addFilter('entity_id', (int)$entityId, 'and');
        }
        $this->load();
        return $this;
    }
}