<?php

class Mage_Sales_Model_Quote_Rule_Action_Quote_Item extends Mage_Sales_Model_Quote_Rule_Action_Abstract
{
    public function loadAttributes()
    {
        $this->setAttributeOption(array(
            'product_id'=>'Product ID',
            'sku'=>'SKU',
            'qty'=>'Quantity',
            'brand'=>'Brand',
            'weight'=>'Weight',
            'price'=>'Price',
        ));
        return $this;
    }
    
    public function loadArray($arr)
    {
        $this->addData(array(
            'attribute'=>$arr['attribute'],
            'operator'=>$arr['operator'],
            'value'=>$arr['value'],
            'item_number'=>$arr['item_number'],
            'item_qty'=>$arr['item_qty'],
        ));
        return parent::loadArray($arr);
    }
    
    public function toArray(array $arrAttributes = array())
    {
        $arr = array(
            'type'=>'quote_item', 
            'attribute'=>$this->getAttribute(),
            'operator'=>$this->getOperator(),
            'value'=>$this->getValue(),
            'item_number'=>$this->getItemNumber(),
            'item_qty'=>$this->getItemQty(),
        );
        return $arr;
    }
    
    public function toString($format='')
    {
        $str = "Update item # ".$this->getItemNumber()." ".$this->getAttributeName()
            ." ".$this->getOperatorName()." ".$this->getValueName()
            ." for ".$this->getItemQty()." item".($this->getItemQty()>1 ? 's' : '');
        return $str;
    }
    
    public function updateQuote(Mage_Sales_Model_Quote $quote)
    {
        return $this;
    }
}