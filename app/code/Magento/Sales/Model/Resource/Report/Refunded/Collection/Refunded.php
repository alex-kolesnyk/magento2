<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Sales report refunded collection
 *
 * @category    Magento
 * @package     Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Sales_Model_Resource_Report_Refunded_Collection_Refunded
    extends Magento_Sales_Model_Resource_Report_Refunded_Collection_Order
{
    /**
     * Initialize custom resource model
     *
     * @param Magento_Core_Model_Event_Manager $eventManager
     * @param Magento_Data_Collection_Db_FetchStrategyInterface $fetchStrategy
     * @param Magento_Core_Model_EntityFactory $entityFactory
     * @param Magento_Sales_Model_Resource_Report $resource
     */
    public function __construct(
        Magento_Core_Model_Event_Manager $eventManager,
        Magento_Data_Collection_Db_FetchStrategyInterface $fetchStrategy,
        Magento_Core_Model_EntityFactory $entityFactory,
        Magento_Sales_Model_Resource_Report $resource
    ) {
        $resource->init('sales_refunded_aggregated');
        parent::__construct($eventManager, $fetchStrategy, $entityFactory, $resource);
    }
}
