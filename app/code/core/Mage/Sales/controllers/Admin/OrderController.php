<?php

class Mage_Sales_OrderController extends Mage_Core_Controller_Admin_Action
{
    public function gridAction()
    {
        $quotes = Mage::getModel('sales_resource', 'quote_collection');
        $quotes->addAttributeToSelect('self');
        $quotes->addAttributeToSelect('item', 'row_total');
        $quotes->loadData();
        echo "<pre>"; print_r($quotes->getItems()); die;
    }
}