<?php
/**
 * Adminhtml invoice items grid
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Michael Bessolov <michael@varien.com>
 */

class Mage_Adminhtml_Block_Sales_Cmemo_Create_Items extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('invoice_items_grid');
        $this->setTemplate('sales/cmemo/create/items.phtml');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('sales/invoice_item_collection')
            ->addAttributeToSelect('*')
            ->setInvoiceFilter(Mage::registry('sales_invoice')->getInvoice()->getId())
        ;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getInvoice()
    {
        return Mage::registry('sales_invoice');
    }

    public function getItemData($invoiceItemId)
    {
        $data = $this->getInvoice()->getData();
        if (isset($data['items']) && isset($data['items'][$invoiceItemId])) {
            return $data['items'][$invoiceItemId];
        }
        return null;
   }

}
