<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Launcher
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Link Tracker resource model
 *
 * @category    Mage
 * @package     Mage_Launcher
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Launcher_Model_Resource_LinkTracker extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('launcher_link_tracker', 'link_id');
    }
}
