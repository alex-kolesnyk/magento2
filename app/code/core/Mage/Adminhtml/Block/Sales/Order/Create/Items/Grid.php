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
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales order create items grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Ivan Chepurnryi <mitch@varien.com>
 * @author      Michael Bessolov <michael@varien.com>
 */

class Mage_Adminhtml_Block_Sales_Order_Create_Items_Grid extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_create_search_grid');
        $this->setTemplate('sales/order/create/items/grid.phtml');
//        $this->setRowClickCallback('sc_searchRowClick');
//        $this->setCheckboxCheckCallback('sc_registerSearchProduct');
//        $this->setRowInitCallback('sc_searchRowInit');
//        $this->setDefaultSort('id');
//        $this->setUseAjax(true);
    }

    public function getItems()
    {
        return $this->getParentBlock()->getItems();
    }

    public function getIsOldCustomer()
    {
        return $this->getParentBlock()->getIsOldCustomer();
    }

    public function getSession()
    {
        return $this->getParentBlock()->getSession();
    }

}
