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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2010 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Core Resource Resource Model
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Resource_Resource extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Resource versions cache
     *
     * @var array
     */
    protected static $_versions = null;

    /**
     * Resource data versions cache array
     * @var array
     */
    protected static $_dataVersions = null;

    /**
     * Define main table
     *
     */
    protected function _construct()
    {
        $this->_init('core/resource', 'store_id');
    }

    /**
     * Fill static versions arrays
     *
     */
    protected function _loadVersionData()
    {
        if (is_null(self::$_versions) || is_null(self::$_dataVersions)) {
            self::$_versions     = array();
            self::$_dataVersions = array();

            if ($this->_getReadAdapter()->isTableExists($this->getMainTable())) {
                $select = $this->_getReadAdapter()->select()
                    ->from($this->getMainTable(), array('code', 'version', 'data_version'));
                $rowset = $this->_getReadAdapter()->fetchAll($select);
                foreach ($rowset as $row) {
                    self::$_versions[$row['code']] = $row['version'];
                    self::$_dataVersions[$row['code']] = $row['data_version'];
                }
            }
        }
    }

    /**
     * Get Module version from DB
     *
     * @param string $moduleName
     * @return string
     */
    function getDbVersion($resName)
    {
        if (!$this->_getReadAdapter()) {
            return false;
        }

        $this->_loadVersionData();

        return isset(self::$_versions[$resName]) ? self::$_versions[$resName] : false;
    }

    /**
     * Set module version into DB
     *
     * @param   string $moduleName
     * @param   string $version
     * @return  int
     */
    function setDbVersion($resName, $version)
    {
        $data = array(
            'code'    => $resName,
            'version' => $version,
        );

        if ($this->getDbVersion($resName)) {
            self::$_versions[$resName] = $version;
            return $this->_getWriteAdapter()->update($this->getMainTable(), $data, array('code=?' => $resName));
        } else {
            self::$_versions[$resName] = $version;
            return $this->_getWriteAdapter()->insert($this->getMainTable(), $data);
        }
    }

    /**
     * Get resource data version
     *
     * @param string $resName
     * @return string | false
     */
    public function getDataVersion($resName)
    {
        if (!$this->_getReadAdapter()) {
            return false;
        }

        $this->_loadVersionData();

        return isset(self::$_dataVersions[$resName]) ? self::$_dataVersions[$resName] : false;
    }

    /**
     * Specify resource data version
     *
     * @param string $resName
     * @param string $version
     * @return Mage_Core_Model_Mysql4_Resource
     */
    public function setDataVersion($resName, $version)
    {
        $data = array(
            'code'         => $resName,
            'data_version' => $version
        );

        if ($this->getDbVersion($resName) || $this->getDataVersion($resName)) {
            self::$_dataVersions[$resName] = $version;
            $this->_getWriteAdapter()->update($this->getMainTable(), $data, array('code=?' => $resName));
        } else {
            self::$_dataVersions[$resName] = $version;
            $this->_getWriteAdapter()->insert($this->getMainTable(), $data);
        }

        return $this;
    }
}
