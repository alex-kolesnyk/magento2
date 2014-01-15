<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_GoogleShopping
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Google Content Item Model
 *
 * @category   Magento
 * @package    Magento_GoogleShopping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GoogleShopping\Model\Service;

class Item extends \Magento\GoogleShopping\Model\Service
{
    /**
     * @var \Magento\GoogleShopping\Helper\Data|null
     */
    protected $_gsData = null;

    /**
     * Date
     *
     * @var \Magento\Core\Model\Date|null
     */
    protected $_date;

    /**
     * @param \Magento\Core\Model\Log\AdapterFactory $logAdapterFactory
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\GoogleShopping\Model\Config $config
     * @param \Magento\Gdata\Gshopping\ContentFactory $contentFactory
     * @param \Magento\Core\Model\Date $date
     * @param \Magento\GoogleShopping\Helper\Data $gsData
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Log\AdapterFactory $logAdapterFactory,
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\GoogleShopping\Model\Config $config,
        \Magento\Gdata\Gshopping\ContentFactory $contentFactory,
        \Magento\Core\Model\Date $date,
        \Magento\GoogleShopping\Helper\Data $gsData,
        array $data = array()
    ) {
        $this->_date = $date;
        $this->_gsData = $gsData;
        parent::__construct($logAdapterFactory, $coreRegistry, $config, $contentFactory, $data);
    }

    /**
     * Return Store level Service Instance
     *
     * @param int $storeId
     * @return \Magento\Gdata\Gshopping\Content
     */
    public function getService($storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->getStoreId();
        }
        return parent::getService($storeId);
    }

    /**
     * Insert Item into Google Content
     *
     * @param \Magento\GoogleShopping\Model\Item $item
     * @return \Magento\GoogleShopping\Model\Service\Item
     */
    public function insert($item)
    {
        $service = $this->getService();
        $entry = $service->newEntry();
        $type = $item->getType();
        $type->convertProductToEntry($item->getProduct(), $entry);

        $entry = $service->insertItem($entry);
        $published = $this->convertContentDateToTimestamp($entry->getPublished()->getText());

        $item->setGcontentItemId($entry->getId())
            ->setPublished($published);

        $expires = $this->_getAttributeValue($entry, 'expiration_date');
        if ($expires) {
            $expires = $this->convertContentDateToTimestamp($expires);
            $item->setExpires($expires);
        }
        return $this;
    }

    /**
     * Update Item data in Google Content
     *
     * @param \Magento\GoogleShopping\Model\Item $item
     * @return \Magento\GoogleShopping\Model\Service\Item
     */
    public function update($item)
    {
        $service = $this->getService();
        $query = $this->_buildItemQuery($item);
        $entry = $service->getItem($query);

        $stats = $this->_getEntryStats($entry);
        if (isset($stats['expires'])) {
            $item->setExpires($stats['expires']);
        }
        $entry = $item->getType()->convertProductToEntry($item->getProduct(), $entry);
        $entry = $service->updateItem($entry);

        return $this;
    }

    /**
     * Delete Item from Google Content
     *
     * @param \Magento\GoogleShopping\Model\Item $item
     * @return \Magento\GoogleShopping\Model\Service\Item
     */
    public function delete($item)
    {
        $service = $this->getService();
        $query = $this->_buildItemQuery($item);
        $service->delete($query->getQueryUrl());

        return $this;
    }

    /**
     * Convert Google Content date format to unix timestamp
     * Ex. 2008-12-08T16:57:23Z -> 2008-12-08 16:57:23
     *
     * @param string Google Content datetime
     * @return int
     */
    public function convertContentDateToTimestamp($gContentDate)
    {
        return $this->_date->date(null, $gContentDate);
    }

    /**
     * Return Google Content Item Attribute Value
     *
     * @param \Magento\Gdata\Gshopping\Entry $entry
     * @param string $name Google Content attribute name
     * @return string|null Attribute value
     */
    protected function _getAttributeValue($entry, $name)
    {
        $attribute = $entry->getContentAttributeByName($name);
        return ($attribute instanceof \Magento\Gdata\Gshopping\Extension\Attribute)
            ? $attribute->text
            : null;
    }

    /**
     * Retrieve item query for Google Content
     *
     * @param \Magento\GoogleShopping\Model\Item $item
     * @return \Magento\Gdata\Gshopping\ItemQuery
     */
    protected function _buildItemQuery($item)
    {
        $storeId = $item->getStoreId();
        $service = $this->getService($storeId);

        $countryInfo = $this->getConfig()->getTargetCountryInfo($storeId);
        $itemId = $this->_gsData->buildContentProductId($item->getProductId(), $item->getStoreId());

        $query = $service->newItemQuery()
            ->setId($itemId)
            ->setTargetCountry($this->getConfig()->getTargetCountry($storeId))
            ->setLanguage($countryInfo['language']);

        return $query;
    }

    /**
     * Return item stats array based on Zend Gdata Entry object
     *
     * @param \Magento\Gdata\Gshopping\Entry $entry
     * @return array
     */
    protected function _getEntryStats($entry)
    {
        $result = array();
        $expirationDate = $entry->getContentAttributeByName('expiration_date');
        if ($expirationDate instanceof \Magento\Gdata\Gshopping\Extension\Attribute) {
            $result['expires'] = $this->convertContentDateToTimestamp($expirationDate->text);
        }

        return $result;
    }
}
