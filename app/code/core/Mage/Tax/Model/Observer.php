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


class Mage_Tax_Model_Observer
{
    public function catalog_block_product_list_collection($observer)
    {
        $collection = $observer->getEvent()->getCollection();

        $hlp = Mage::helper('tax');
        if (!$hlp->showInCatalog($collection->getStoreId())) {
            return $this;
        }

        foreach ($collection as $product) {
            $hlp->updateProductTax($product);
        }

        return $this;
    }

    public function catalog_block_product_view($observer)
    {
        $product = $observer->getEvent()->getProduct();
        Mage::helper('tax')->updateProductTax($product);
        return $this;
    }
}