<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_ProductAlert
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Product alert for changed price resource model
 */
namespace Magento\ProductAlert\Model\Resource;

class Price extends \Magento\ProductAlert\Model\Resource\AbstractResource
{
    /**
     * @var \Magento\Core\Model\DateFactory
     */
    protected $_dateFactory;

    /**
     * @param \Magento\App\Resource $resource
     * @param \Magento\Core\Model\DateFactory $dateFactory
     */
    public function __construct(
        \Magento\App\Resource $resource,
        \Magento\Core\Model\DateFactory $dateFactory
    ) {
        $this->_dateFactory = $dateFactory;
        parent::__construct($resource);
    }

    /**
     * Initialize connection
     *
     */
    protected function _construct()
    {
        $this->_init('product_alert_price', 'alert_price_id');
    }

    /**
     * Before save process, check exists the same alert
     *
     * @param \Magento\Core\Model\AbstractModel $object
     * @return \Magento\ProductAlert\Model\Resource\Price
     */
    protected function _beforeSave(\Magento\Core\Model\AbstractModel $object)
    {
        if (is_null($object->getId()) && $object->getCustomerId()
                && $object->getProductId() && $object->getWebsiteId()) {
            if ($row = $this->_getAlertRow($object)) {
                $price = $object->getPrice();
                $object->addData($row);
                if ($price) {
                    $object->setPrice($price);
                }
                $object->setStatus(0);
            }
        }
        if (is_null($object->getAddDate())) {
            $object->setAddDate($this->_dateFactory->create()->gmtDate());
        }
        return parent::_beforeSave($object);
    }
}
