<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Oauth
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Oauth Application resource collection model
 *
 * @category    Mage
 * @package     Mage_Oauth
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Oauth_Model_Resource_Consumer_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize collection model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mage_Oauth_Model_Consumer', 'Mage_Oauth_Model_Resource_Consumer');
    }
}
