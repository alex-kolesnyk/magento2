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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Review
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * API2 for reviews collection
 *
 * @category   Mage
 * @package    Mage_Review
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Review_Model_Api2_Reviews_Rest_Guest_V1 extends Mage_Review_Model_Api2_Reviews_Rest
{
    /**
     * Create new review
     *
     * @param $data
     * @return bool
     */
    protected function _create(array $data)
    {
        /** @var $reviewHelper Mage_Review_Helper_Data */
        $reviewHelper = Mage::helper('review');
        if (!$reviewHelper->getIsGuestAllowToWrite()) {
            $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
        }

        $required = array('product_id', 'nickname', 'title', 'detail');
        $notEmpty = array('product_id', 'nickname', 'title', 'detail');
        if (!isset($data['store_id']) || empty($data['store_id'])) {
            $data['store_id'] = Mage::app()->getDefaultStoreView()->getId();
        }
        $data['stores'] = array($data['store_id']);

        $this->_validate($data, $required, $notEmpty);
        $data['status_id'] = Mage_Review_Model_Review::STATUS_PENDING;
        $data['customer_id'] = $this->getApiUser()->getUserId();
        return parent::_create($data);
    }

    /**
     * Validate status input data
     *
     * @param array $data
     * @param array $required
     * @param array $notEmpty
     */
    protected function _validate(array $data, array $required = array(), array $notEmpty = array())
    {
        parent::_validate($data, $required, $notEmpty);
        $this->_validateStores($data['stores']);
    }

    /**
     * Prepare collection for retrieve
     *
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    protected function _prepareRetrieveCollection()
    {
        /** @var $collection Mage_Review_Model_Resource_Review_Collection */
        $collection = Mage::getResourceModel('review/review_collection');
        $this->_applyProductFilter($collection);
        $this->_applyCollectionModifiers($collection);
        // apply store filter
        $storeId = $this->getRequest()->getParam('store_id');
        if ($storeId) {
            $this->_validateStores(array($storeId));
            $collection->addStoreFilter($storeId);
        }
        $collection->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED);
        return $collection;
    }
}
