<?php
/**
 * Store loader
 *
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */
class Mage_Core_Model_Store_Storage_Default implements Mage_Core_Model_Store_StorageInterface
{
    /**
     * Application store object
     *
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * Application website object
     *
     * @var Mage_Core_Model_Website
     */
    protected $_website;

    /**
     * Application website object
     *
     * @var Mage_Core_Model_Store_Group
     */
    protected $_group;

    /**
     * @param Mage_Core_Model_StoreFactory $storeFactory
     * @param Mage_Core_Model_Website_Factory $websiteFactory
     * @param Mage_Core_Model_Store_Group_Factory $groupFactory
     */
    public function __construct(
        Mage_Core_Model_StoreFactory $storeFactory,
        Mage_Core_Model_Website_Factory $websiteFactory,
        Mage_Core_Model_Store_Group_Factory $groupFactory
    ) {

        $this->_store = $storeFactory->create();
        $this->_store->setId(Mage_Core_Model_AppInterface::DISTRO_STORE_ID);
        $this->_store->setCode(Mage_Core_Model_AppInterface::DISTRO_STORE_CODE);
        $this->_website = $websiteFactory->create();
        $this->_group = $groupFactory->createFromArray();
    }

    /**
     * Initialize current applicaition store
     */
    public function initCurrentStore()
    {
        //not applicable for default storage
    }

    /**
     * Allow or disallow single store mode
     *
     * @param bool $value
     */
    public function setIsSingleStoreModeAllowed($value)
    {
        //not applicable for default storage
    }

    /**
     * Check if store has only one store view
     *
     * @return bool
     */
    public function hasSingleStore()
    {
        return false;
    }

    /**
     * Retrieve application store object
     *
     * @param null|string|bool|int|Mage_Core_Model_Store $id
     * @return Mage_Core_Model_Store
     */
    public function getStore($id = null)
    {
        return $this->_store;
    }

    /**
     * Retrieve stores array
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return Mage_Core_Model_Store[]
     */
    public function getStores($withDefault = false, $codeKey = false)
    {
        return array();
    }

    /**
     * Retrieve application website object
     *
     * @param null|bool|int|string|Mage_Core_Model_Website $id
     * @return Mage_Core_Model_Website
     * @throws Mage_Core_Exception
     */
    public function getWebsite($id = null)
    {
        if ($id instanceof Mage_Core_Model_Website) {
            return $id;
        }

        return $this->_website;
    }

    /**
     * Get loaded websites
     *
     * @param bool $withDefault
     * @param bool|string $codeKey
     * @return Mage_Core_Model_Website[]
     */
    public function getWebsites($withDefault = false, $codeKey = false)
    {
        $websites = array();

        if ($withDefault) {
            $key = $codeKey ? $this->_website->getCode() : $this->_website->getId();
            $websites[$key] = $this->_website;
        }

        return $websites;
    }

    /**
     * Retrieve application store group object
     *
     * @param null|Mage_Core_Model_Store_Group|string $id
     * @return Mage_Core_Model_Store_Group
     * @throws Mage_Core_Exception
     */
    public function getGroup($id = null)
    {
        if ($id instanceof Mage_Core_Model_Store_Group) {
            return $id;
        }

        return $this->_group;
    }

    /**
     * Prepare array of store groups
     * can be filtered to contain default store group or not by $withDefault flag
     * depending on flag $codeKey array keys can be group id or group code
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return Mage_Core_Model_Store_Group[]
     */
    public function getGroups($withDefault = false, $codeKey = false)
    {
        $groups = array();

        if ($withDefault) {
            $key = $codeKey ? $this->_group->getCode() : $this->_group->getId();
            $groups[$key] = $this->_group;
        }
        return $groups;
    }

    /**
     * Reinitialize store list
     */
    public function reinitStores()
    {
        //not applicable for default storage
    }

    /**
     * Retrieve default store for default group and website
     *
     * @return Mage_Core_Model_Store
     */
    public function getDefaultStoreView()
    {
       return null;
    }

    /**
     *  Unset website by id from app cache
     *
     * @param null|bool|int|string|Mage_Core_Model_Website $id
     */
    public function clearWebsiteCache($id = null)
    {
        //not applicable for default storage
    }

    /**
     * Get either default or any store view
     *
     * @return Mage_Core_Model_Store
     */
    public function getAnyStoreView()
    {
        return null;
    }

    /**
     * Set current default store
     *
     * @param string $store
     */
    public function setCurrentStore($store)
    {

    }

    /**
     * @throws Mage_Core_Model_Store_Exception
     */
    public function throwStoreException()
    {
        //not applicable for default storage
    }

    /**
     * Get current store code
     *
     * @return string
     */
    public function getCurrentStore()
    {
        return $this->_store->getCode();
    }
}
