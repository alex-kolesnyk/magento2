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
 * Sales Mysql resource helper model
 */
namespace Magento\Sales\Model\Resource\Helper;

class Mysql4 extends \Magento\Core\Model\Resource\Helper\Mysql4
    implements \Magento\Sales\Model\Resource\Helper\HelperInterface
{
    /**
     * @var \Magento\Reports\Model\Resource\Helper\Mysql4
     */
    protected $_reportsResourceHelper;

    /**
     * @param \Magento\Reports\Model\Resource\Helper\Mysql4 $reportsResourceHelper
     * @param string $modulePrefix
     */
    public function __construct(
        \Magento\Reports\Model\Resource\Helper\Mysql4 $reportsResourceHelper,
        $modulePrefix = 'sales'
    ) {
        parent::__construct($modulePrefix);
        $this->_reportsResourceHelper = $reportsResourceHelper;
    }

    /**
     * Update rating position
     *
     * @param string $aggregation One of \Magento\Sales\Model\Resource\Report\Bestsellers::AGGREGATION_XXX constants
     * @param array $aggregationAliases
     * @param string $mainTable
     * @param string $aggregationTable
     * @return \Magento\Sales\Model\Resource\Helper\Mysql4
     */
    public function getBestsellersReportUpdateRatingPos($aggregation, $aggregationAliases,
        $mainTable, $aggregationTable
    ) {
        if ($aggregation == $aggregationAliases['monthly']) {
            $this->_reportsResourceHelper->updateReportRatingPos('month', 'qty_ordered', $mainTable, $aggregationTable);
        } elseif ($aggregation == $aggregationAliases['yearly']) {
            $this->_reportsResourceHelper->updateReportRatingPos('year', 'qty_ordered', $mainTable, $aggregationTable);
        } else {
            $this->_reportsResourceHelper->updateReportRatingPos('day', 'qty_ordered', $mainTable, $aggregationTable);
        }

        return $this;
    }
}
