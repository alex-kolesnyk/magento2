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
 * Service model for managing statuses information. Statuses are just records with code, message and any
 * additional data. The model helps to keep track and manipulate statuses, that different modules want to set
 * to owner object of this model.
 *
 * @category    Magento
 * @package     Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Sales_Model_Status_List
{
    /**
     * Status information entities
     *
     * @var array
     */
    protected $_items = array();

    /**
     * Adds status information to the list of items.
     *
     * @param string|null $origin Usually a name of module, that adds this status
     * @param int|null $code Code of status, unique for origin, that sets it
     * @param string|null $message Status message
     * @param Magento_Object|null $additionalData Any additional data, that caller would like to store
     * @return Magento_Sales_Model_Status_List
     */
    public function addItem($origin = null, $code = null, $message = null, $additionalData = null)
    {
        $this->_items[] = array(
            'origin' => $origin,
            'code' => $code,
            'message' => $message,
            'additionalData' => $additionalData
        );
        return $this;
    }

    /**
     * Retrieves all items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Removes items, that have parameters equal to passed in $params.
     * Returns items removed.
     * $params can have following keys (if not set - then any item is good for this key):
     *   'origin', 'code', 'message'
     *
     * @param array $params
     * @return array
     */
    public function removeItemsByParams($params)
    {
        $items = $this->getItems();
        if (!$items) {
            return array();
        }

        $indexes = array();
        $paramKeys = array('origin', 'code', 'message');
        foreach ($items as $index => $item) {
            $remove = true;
            foreach ($paramKeys as $key) {
                if (!isset($params[$key])) {
                    continue;
                }
                if ($params[$key] != $item[$key]) {
                    $remove = false;
                    break;
                }
            }
            if ($remove) {
                $indexes[] = $index;
            }
        }

        return $this->removeItems($indexes);
    }

    /**
     * Removes items at mentioned index/indexes.
     * Returns items removed.
     *
     * @param int|array $indexes
     * @return array
     */
    public function removeItems($indexes)
    {
        if (!array($indexes)) {
            $indexes = array($indexes);
        }
        if (!$indexes) {
            return array();
        }

        $items = $this->getItems();
        if (!$items) {
            return array();
        }

        $newItems = array();
        $removedItems = array();
        foreach ($items as $indexNow => $item) {
            if (in_array($indexNow, $indexes)) {
                $removedItems[] = $item;
            } else {
                $newItems[] = $item;
            }
        }

        $this->_items = $newItems;
        return $removedItems;
    }

    /**
     * Clears list from all items
     *
     * @return Magento_Sales_Model_Status_List
     */
    public function clear()
    {
        $this->_items = array();
        return $this;
    }
}
