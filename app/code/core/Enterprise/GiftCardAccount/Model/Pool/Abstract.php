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
 * @category   Enterprise
 * @package    Enterprise_GiftCardAccount
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


abstract class Enterprise_GiftCardAccount_Model_Pool_Abstract extends Mage_Core_Model_Abstract
{
    const STATUS_FREE = 0;
    const STATUS_USED = 1;

    protected $_pool_percent_used = 0;
    protected $_pool_size = 0;
    protected $_pool_free_size = 0;

    /**
     * Return first free code
     *
     * @return string
     */
    public function shift()
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('status', self::STATUS_FREE)
            ->setPageSize(1)
            ->load();

        if (!$items = $collection->getItems()) {
            Mage::throwException(Mage::helper('enterprise_giftcardaccount')->__('No codes left in the pool.'));
        }

        $item = array_shift($items);
        return $item->getId();
    }

    /**
     * Create Adminhtml notice with code pool used percentage
     *
     * @return Enterprise_GiftCardAccount_Model_Pool_Abstract
     */
    public function addNotice()
    {
        $this->_loadPoolUsageInfo();

        $function = 'addNotice';
        if ($this->_pool_percent_used == 100) {
            $function = 'addError';
        }

        Mage::getSingleton('adminhtml/session')->$function(
            Mage::helper('enterprise_giftcardaccount')->__(
                'Code pool is %d%% used (%d free of %d total).',
                $this->_pool_percent_used,
                $this->_pool_free_size,
                $this->_pool_size)
        );

        return $this;
    }

    /**
     * Load code pool usage info
     *
     * @return Enterprise_GiftCardAccount_Model_Pool_Abstract
     */
    protected function _loadPoolUsageInfo()
    {
        $this->_pool_size = $this->getCollection()->getSize();
        $this->_pool_free_size = $this->getCollection()
            ->addFieldToFilter('status', self::STATUS_FREE)
            ->getSize();
        if (!$this->_pool_size) {
            $this->_pool_percent_used = 100;
        } else {
            $this->_pool_percent_used = 100-round($this->_pool_free_size/($this->_pool_size/100));
        }
        return $this;
    }

    /**
     * Delete free codes from pool
     *
     * @return Enterprise_GiftCardAccount_Model_Pool_Abstract
     */
    public function cleanupFree()
    {
        $this->getCollection()
            ->addFieldToFilter('status', self::STATUS_FREE)
            ->walk('delete');

        return $this;
    }
}