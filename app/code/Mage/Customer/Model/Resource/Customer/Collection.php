<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Customer
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Customers collection
 *
 * @category    Mage
 * @package     Mage_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Customer_Model_Resource_Customer_Collection extends Mage_Eav_Model_Entity_Collection_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('Mage_Customer_Model_Customer', 'Mage_Customer_Model_Resource_Customer');
    }

    /**
     * Group result by customer email
     *
     * @return Mage_Customer_Model_Resource_Customer_Collection
     */
    public function groupByEmail()
    {
        $this->getSelect()
            ->from(
                array('email' => $this->getEntity()->getEntityTable()),
                array('email_count' => new Zend_Db_Expr('COUNT(email.entity_id)'))
            )
            ->where('email.entity_id = e.entity_id')
            ->group('email.email');

        return $this;
    }

    /**
     * Add Name to select
     *
     * @return Mage_Customer_Model_Resource_Customer_Collection
     */
    public function addNameToSelect()
    {
        $fields = array();
        $customerAccount = Mage::getConfig()->getFieldset('customer_account');
        foreach ($customerAccount as $code => $node) {
            if ($node->is('name')) {
                $fields[$code] = $code;
            }
        }

        $adapter = $this->getConnection();
        $concatenate = array();
        if (isset($fields['prefix'])) {
            $concatenate[] = $adapter->getCheckSql(
                '{{prefix}} IS NOT NULL AND {{prefix}} != \'\'',
                $adapter->getConcatSql(array('LTRIM(RTRIM({{prefix}}))', '\' \'')),
                '\'\'');
        }
        $concatenate[] = 'LTRIM(RTRIM({{firstname}}))';
        $concatenate[] = '\' \'';
        if (isset($fields['middlename'])) {
            $concatenate[] = $adapter->getCheckSql(
                '{{middlename}} IS NOT NULL AND {{middlename}} != \'\'',
                $adapter->getConcatSql(array('LTRIM(RTRIM({{middlename}}))', '\' \'')),
                '\'\'');
        }
        $concatenate[] = 'LTRIM(RTRIM({{lastname}}))';
        if (isset($fields['suffix'])) {
            $concatenate[] = $adapter
                    ->getCheckSql('{{suffix}} IS NOT NULL AND {{suffix}} != \'\'',
                $adapter->getConcatSql(array('\' \'', 'LTRIM(RTRIM({{suffix}}))')),
                '\'\'');
        }

        $nameExpr = $adapter->getConcatSql($concatenate);

        $this->addExpressionAttributeToSelect('name', $nameExpr, $fields);

        return $this;
    }

    /**
     * Get SQL for get record count
     *
     * @return Magento_DB_Select
     */
    public function getSelectCountSql()
    {
        $select = parent::getSelectCountSql();
        $select->resetJoinLeft();

        return $select;
    }

    /**
     * Reset left join
     *
     * @param int $limit
     * @param int $offset
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = parent::_getAllIdsSelect($limit, $offset);
        $idsSelect->resetJoinLeft();
        return $idsSelect;
    }
}
