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
 * Reports summary collection
 *
 * @category    Magento
 * @package     Magento_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Entity\Summary\Collection;

class AbstractCollection extends \Magento\Data\Collection
{
    /**
     * Entity collection for summaries
     *
     * @var \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected $_entityCollection;

    /**
     * @var \Magento\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Stdlib\DateTime $dateTime
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Stdlib\DateTime $dateTime
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($entityFactory);
    }

    /**
     * Filters the summaries by some period
     *
     * @param string $periodType
     * @param string|int|null $customStart
     * @param string|int|null $customEnd
     * @return \Magento\Reports\Model\Resource\Entity\Summary\Collection\AbstractCollection
     */
    public function setSelectPeriod($periodType, $customStart = null, $customEnd = null)
    {
        switch ($periodType) {
            case "24h":
                $customStart = $this->dateTime->toTimestamp(true) - 86400;
                $customEnd   = $this->dateTime->toTimestamp(true);
                break;

            case "7d":
                $customStart = $this->dateTime->toTimestamp(true) - 604800;
                $customEnd   = $this->dateTime->toTimestamp(true);
                break;

            case "30d":
                $customStart = $this->dateTime->toTimestamp(true) - 2592000;
                $customEnd   = $this->dateTime->toTimestamp(true);
                break;

            case "1y":
                $customStart = $this->dateTime->toTimestamp(true) - 31536000;
                $customEnd   = $this->dateTime->toTimestamp(true);
                break;

            default:
                if (is_string($customStart)) {
                    $customStart = strtotime($customStart);
                }
                if (is_string($customEnd)) {
                    $customEnd = strtotime($customEnd);
                }
                break;

        }


        return $this;
    }

    /**
     * Set date period
     *
     * @param int $period
     * @return \Magento\Reports\Model\Resource\Entity\Summary\Collection\AbstractCollection
     */
    public function setDatePeriod($period)
    {
        return $this;
    }

    /**
     * Set store filter
     *
     * @param int $storeId
     * @return \Magento\Reports\Model\Resource\Entity\Summary\Collection\AbstractCollection
     */
    public function setStoreFilter($storeId)
    {
        return $this;
    }

    /**
     * Return collection for summaries
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    public function getCollection()
    {
        if (empty($this->_entityCollection)) {
            $this->_initCollection();
        }
        return $this->_entityCollection;
    }

    /**
     * Init collection
     *
     * @return \Magento\Reports\Model\Resource\Entity\Summary\Collection\AbstractCollection
     */
    protected function _initCollection()
    {
        return $this;
    }
}
