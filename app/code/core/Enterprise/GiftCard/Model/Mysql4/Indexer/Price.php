<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Enterprise
 * @package    Enterprise_GiftCard
 * @copyright  Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * GiftCard product price indexer resource model
 *
 * @category   Enterprise
 * @package    Enterprise_GiftCard
 */
class Enterprise_GiftCard_Model_Mysql4_Indexer_Price
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Default
{
    /**
     * Prepare giftCard products prices in temporary index table
     *
     * @param int|array $entityIds  the entity ids limitation
     * @return Enterprise_GiftCard_Model_Mysql4_Indexer_Price
     */
    protected function _prepareFinalPriceData($entityIds = null)
    {
        $this->_prepareDefaultFinalPriceTable();

        $write  = $this->_getWriteAdapter();
        $select = $write->select()
            ->from(array('e' => $this->getTable('catalog/product')), array('entity_id'))
            ->join(
                array('cg' => $this->getTable('customer/customer_group')),
                '',
                array('customer_group_id'));
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');
        $select->columns(array('website_id'), 'cw')
            ->columns(array('tax_class_id'  => new Zend_Db_Expr('0')))
            ->where('e.type_id=?', $this->getTypeId());

        // add enable products limitation
        $statusCond = $write->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id', $statusCond, true);

        $allowOpenAmount = $this->_addAttributeToSelect($select, 'allow_open_amount', 'e.entity_id', 'cs.store_id');
        $openAmounMin    = $this->_addAttributeToSelect($select, 'open_amount_min', 'e.entity_id', 'cs.store_id');
//        $openAmounMax    = $this->_addAttributeToSelect($select, 'open_amount_max', 'e.entity_id', 'cs.store_id');

        $attrAmounts     = $this->_getAttribute('giftcard_amounts');
        // join giftCard amounts table
        $select->joinLeft(
            array('gca' => $this->getTable('enterprise_giftcard/amount')),
            'gca.entity_id = e.entity_id AND gca.attribute_id = ' . $attrAmounts->getAttributeId()
                . ' AND (gca.website_id = cw.website_id OR gca.website_id = 0)',
            array());

        $amountsExpr    = new Zend_Db_Expr("IF(gca.value_id IS NULL, NULL, MIN(gca.value))");
        $openAmountExpr = new Zend_Db_Expr("IF({$allowOpenAmount} = 1, IF({$openAmounMin} > 0, {$openAmounMin}, 0), 'undefined')");
        $priceExpr      = new Zend_Db_Expr("ROUND("
            . " CASE {$openAmountExpr}"
            . " WHEN 'undefined'"
            . " THEN IF({$amountsExpr} IS NULL, 0, {$amountsExpr})"
            . " ELSE IF({$amountsExpr} IS NULL, {$openAmountExpr}, LEAST({$amountsExpr}, {$openAmountExpr}))"
            . " END, 4)");

        $select->group(array('e.entity_id', 'cg.customer_group_id', 'cw.website_id'))
            ->columns(array(
                'price'         => new Zend_Db_Expr('NULL'),
                'final_price'   => $priceExpr,
                'min_price'     => $priceExpr,
                'max_price'     => new Zend_Db_Expr('NULL'),
                'tier_price'    => new Zend_Db_Expr('NULL'),
            ));

        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        $query = $select->insertFromSelect($this->_getDefaultFinalPriceTable());
        $write->query($query);

        return $this;
    }
}
