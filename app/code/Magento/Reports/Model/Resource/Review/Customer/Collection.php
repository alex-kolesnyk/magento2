<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Reports
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Report Customers Review collection
 *
 * @category    Magento
 * @package     Magento_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Review\Customer;

class Collection extends \Magento\Review\Model\Resource\Review\Collection
{
    /**
     * @var \Magento\Customer\Model\Resource\Customer
     */
    protected $_customerResource;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Logger $logger
     * @param \Magento\Review\Helper\Data $reviewData
     * @param \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Rating\Model\Rating\Option\VoteFactory $voteFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Resource\Customer $customerResource
     * @param \Magento\Core\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Logger $logger,
        \Magento\Review\Helper\Data $reviewData,
        \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Rating\Model\Rating\Option\VoteFactory $voteFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Resource\Customer $customerResource,
        \Magento\Core\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->_customerResource = $customerResource;
        parent::__construct(
            $eventManager, $logger, $reviewData, $fetchStrategy,
            $entityFactory, $voteFactory, $storeManager, $resource
        );
    }

    /**
     * Init Select
     *
     * @return \Magento\Reports\Model\Resource\Review\Customer\Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->_joinCustomers();
        return $this;
    }

    /**
     * Join customers
     *
     * @return \Magento\Reports\Model\Resource\Review\Customer\Collection
     */
    protected function _joinCustomers()
    {
        /** @var $adapter \Magento\DB\Adapter\AdapterInterface */
        $adapter            = $this->getConnection();
        /** @var $firstnameAttr \Magento\Eav\Model\Entity\Attribute */
        $firstnameAttr      = $this->_customerResource->getAttribute('firstname');
        /** @var $lastnameAttr \Magento\Eav\Model\Entity\Attribute */
        $lastnameAttr       = $this->_customerResource->getAttribute('lastname');

        $firstnameCondition = array('table_customer_firstname.entity_id = detail.customer_id');

        if ($firstnameAttr->getBackend()->isStatic()) {
            $firstnameField = 'firstname';
        } else {
            $firstnameField = 'value';
            $firstnameCondition[] = $adapter->quoteInto('table_customer_firstname.attribute_id = ?',
                (int)$firstnameAttr->getAttributeId());
        }

        $this->getSelect()->joinInner(
            array('table_customer_firstname' => $firstnameAttr->getBackend()->getTable()),
            implode(' AND ', $firstnameCondition),
            array());


        $lastnameCondition  = array('table_customer_lastname.entity_id = detail.customer_id');
        if ($lastnameAttr->getBackend()->isStatic()) {
            $lastnameField = 'lastname';
        } else {
            $lastnameField = 'value';
            $lastnameCondition[] = $adapter->quoteInto('table_customer_lastname.attribute_id = ?',
                (int)$lastnameAttr->getAttributeId());
        }

        //Prepare fullname field result
        $customerFullname = $adapter->getConcatSql(array(
            "table_customer_firstname.{$firstnameField}",
            "table_customer_lastname.{$lastnameField}"
        ), ' ');
        $this->getSelect()->reset(\Zend_Db_Select::COLUMNS)
            ->joinInner(
                array('table_customer_lastname' => $lastnameAttr->getBackend()->getTable()),
                implode(' AND ', $lastnameCondition),
                array())
            ->columns(array(
                'customer_id' => 'detail.customer_id',
                'customer_name' => $customerFullname,
                'review_cnt'    => 'COUNT(main_table.review_id)'))
            ->group('detail.customer_id');

        return $this;
    }

    /**
     * Get select count sql
     *
     * @return string
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->_select;
        $countSelect->reset(\Zend_Db_Select::ORDER);
        $countSelect->reset(\Zend_Db_Select::GROUP);
        $countSelect->reset(\Zend_Db_Select::HAVING);
        $countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(\Zend_Db_Select::COLUMNS);

        $countSelect->columns(new \Zend_Db_Expr('COUNT(DISTINCT detail.customer_id)'));

        return  $countSelect;
    }
}
