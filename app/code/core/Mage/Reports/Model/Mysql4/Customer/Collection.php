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
 * @package    Mage_Reports
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customers Report collection
 *
 * @category   Mage
 * @package    Mage_Reports
 * @author     Dmytro Vasylenko  <dimav@varien.com>
 */
 
class Mage_Reports_Model_Mysql4_Customer_Collection extends Mage_Customer_Model_Entity_Customer_Collection
{
    protected function _construct()
    {
        parent::__construct();
    }
    
    public function addCartInfo()
    {
        foreach ($this->getItems() as $item)
        {        
            $quote = Mage::getResourceModel('sales/quote_collection')
                        ->loadByCustomerId($item->getId());
            
            if (is_object($quote))
            {
                $totals = $quote->getTotals();
                $item->setTotal($totals['subtotal']->getValue());
                $quote_items = Mage::getResourceModel('sales/quote_item_collection')->setQuoteFilter($quote->getId());
                $quote_items->load();
                $item->setItems($quote_items->count());
            } else {
                $item->setItems('0');
                $item->setTotal('0');
            }
            
        }
        return $this;
    }   
}
