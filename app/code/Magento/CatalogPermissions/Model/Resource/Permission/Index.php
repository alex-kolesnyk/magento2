<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_CatalogPermissions
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Permission indexer resource
 *
 * @category    Magento
 * @package     Magento_CatalogPermissions
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_CatalogPermissions_Model_Resource_Permission_Index extends Magento_Index_Model_Resource_Abstract
{
    const XML_PATH_GRANT_BASE = 'catalog/magento_catalogpermissions/';

    /**
     * Store ids
     *
     * @var arrray
     */
    protected $_storeIds           = array();

    /**
     * Data for insert
     *
     * @var array
     */
    protected $_insertData         = array();

    /**
     * Table fields for insert
     *
     * @var array
     */
    protected $_tableFields        = array();

    /**
     * Permission cache
     *
     * @var array
     */
    protected $_permissionCache    = array();

    /**
     * Inheritance of grant appling in categories tree
     *
     * @var array
     */
    protected $_grantsInheritance  = array(
        'grant_catalog_category_view' => 'deny',
        'grant_catalog_product_price' => 'allow',
        'grant_checkout_items' => 'allow'
    );

    /**
     * @var Magento_Core_Model_StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param Magento_Core_Model_Resource $resource
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     */
    public function __construct(
        Magento_Core_Model_Resource $resource,
        Magento_Core_Model_StoreManagerInterface $storeManager
    ) {
        parent::__construct($resource);
        $this->_storeManager = $storeManager;
    }

    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('magento_catalogpermissions_index', 'category_id');
    }

    /**
     * Reindex category permissions
     *
     * @param string|null $categoryPath
     * @return Magento_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function reindex($categoryPath = null)
    {
        $readAdapter  = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();

        $select       = $readAdapter->select()
            ->from($this->getTable('catalog_category_entity'), array('entity_id','path'));
        if (!is_null($categoryPath)) {
            $select->where('path LIKE ?', $categoryPath . '/%')
                ->orWhere('entity_id IN(?)', explode('/', $categoryPath));
        }
        $select->order('level ASC');

        $categoryPath = $readAdapter->fetchPairs($select);
        $categoryIds = array_keys($categoryPath);

        $select = $readAdapter->select()
            ->from(array('permission' => $this->getTable('magento_catalogpermissions')), array(
                'category_id',
                'website_id',
                'customer_group_id',
                'grant_catalog_category_view',
                'grant_catalog_product_price',
                'grant_checkout_items'
            ))
            ->where('permission.category_id IN (?)', $categoryIds);

        $websiteIds = Mage::getModel('Magento_Core_Model_Website')->getCollection()
            ->addFieldToFilter('website_id', array('neq'=>0))
            ->getAllIds();

        $customerGroupIds = Mage::getModel('Magento_Customer_Model_Group')->getCollection()
            ->getAllIds();

        $notEmptyWhere = array();

        foreach (array_keys($this->_grantsInheritance) as $grant) {
            $notEmptyWhere[] = $readAdapter->quoteInto(
                'permission.' . $grant . ' != ?',
                Magento_CatalogPermissions_Model_Permission::PERMISSION_PARENT
            );
        }

        $select->where('(' . implode(' OR ', $notEmptyWhere).  ')');

        $permissions = $readAdapter->fetchAll($select);

        // Delete old index
        if (!empty($categoryIds)) {
            $writeAdapter->delete(
                $this->getMainTable(),
                array('category_id IN (?)' => $categoryIds)
            );
        }

        $this->_permissionCache = array();

        foreach ($permissions as $permission) {
            $uniqKey = $permission['website_id'] . '_' . $permission['customer_group_id'];
            if ($permission['website_id'] === null || $permission['customer_group_id'] === null) {
                    $uniqKey .= '_default';
            }
            $path = $categoryPath[$permission['category_id']];
            $this->_permissionCache[$path][$uniqKey] = $permission;
        }

        unset ($permissions);

        foreach ($this->_permissionCache as &$permissions) {
            foreach (array_keys($permissions) as $uniqKey) {
                $permission = $permissions[$uniqKey];
                if ($permission['website_id'] === null && $permission['customer_group_id'] === null) {
                    foreach ($customerGroupIds as $customerGroupId) {
                        // Apply permissions for all customer groups
                        if (!isset($permissions['_' . $customerGroupId . '_default'])) {
                            $permission['customer_group_id'] = $customerGroupId;
                            $permissions['_' . $customerGroupId . '_default'] = $permission;
                        }
                    }
                    unset($permissions[$uniqKey]);
                }
            }

            foreach (array_keys($permissions) as $uniqKey) {
                $permission = $permissions[$uniqKey];
                if ($permission['website_id'] === null) {
                    foreach ($websiteIds as $websiteId) {
                        if (!isset($permissions[$websiteId . '__default'])
                            && !isset($permissions[$websiteId . '_' . $permission['customer_group_id']])) {
                            // Apply permissions for all websites
                            $permission['website_id'] = $websiteId;
                            $permissions[$websiteId . '_' . $permission['customer_group_id']] = $permission;
                        }
                    }
                } elseif ($permission['customer_group_id'] === null) {
                    foreach ($customerGroupIds as $customerGroupId) {
                        if (!isset($permissions[$permission['website_id'] . '_' . $customerGroupId])) {
                            $permission['customer_group_id'] = $customerGroupId;
                            $permissions[$permission['website_id'] . '_' . $customerGroupId] = $permission;
                        }
                    }
                } else {
                    continue;
                }
                unset($permissions[$uniqKey]);
            }
        }

        $fields =  array_merge(
            array(
                'category_id', 'website_id', 'customer_group_id',
                'grant_catalog_category_view',
                'grant_catalog_product_price',
                'grant_checkout_items'
            )
        );

        $this->_beginInsert('magento_catalogpermissions_index', $fields);

        $permissionDeny = Magento_CatalogPermissions_Model_Permission::PERMISSION_DENY;
        foreach ($categoryPath as $categoryId => $path) {
            $this->_inheritCategoryPermission($path);
            if (isset($this->_permissionCache[$path])) {
                foreach ($this->_permissionCache[$path] as $permission) {
                    if ($permission['grant_catalog_category_view'] == $permissionDeny) {
                        $permission['grant_catalog_product_price'] = $permissionDeny;
                    }
                    if ($permission['grant_catalog_product_price'] == $permissionDeny) {
                        $permission['grant_checkout_items'] = $permissionDeny;
                    }
                    $this->_insert('magento_catalogpermissions_index', array(
                        'category_id'                 => $categoryId,
                        'website_id'                  => $permission['website_id'],
                        'customer_group_id'           => $permission['customer_group_id'],
                        'grant_catalog_category_view' => $permission['grant_catalog_category_view'],
                        'grant_catalog_product_price' => $permission['grant_catalog_product_price'],
                        'grant_checkout_items'        => $permission['grant_checkout_items']
                    ));
                }
            }
        }

        $this->_commitInsert('magento_catalogpermissions_index');
        $this->_permissionCache = array();

        $select = $readAdapter->select()
            ->from($this->getTable('catalog_category_product'), 'product_id')
            ->distinct(true)
            ->where('category_id IN(?)', $categoryIds);

        $productIds = $readAdapter->fetchCol($select);

        $this->reindexProducts($productIds);

        return $this;
    }

    /**
     * Reindex products permissions
     *
     * @param array|string $productIds
     * @return Magento_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function reindexProducts($productIds = null)
    {
        $readAdapter = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();
        /* @var $isActive Magento_Eav_Model_Entity_Attribute */
        $isActive = Mage::getSingleton('Magento_Eav_Model_Config')->getAttribute('catalog_category', 'is_active');

        $selectCategory = $readAdapter->select()
            ->from(
                array('category_product_index' => $this->getTable('catalog_category_product_index')),
                array('product_id', 'store_id'));

        if ($isActive->isScopeGlobal()) {
            $selectCategory
                ->joinLeft(
                    array('category_is_active' => $isActive->getBackend()->getTable()),
                    'category_product_index.category_id = category_is_active.entity_id'
                    . ' AND category_is_active.store_id = 0'
                    . $readAdapter->quoteInto(' AND category_is_active.attribute_id = ?', $isActive->getAttributeId()),
                    array())
                ->where('category_is_active.value = 1');
        } else {
            $whereExpr = $readAdapter->getCheckSql(
                'category_is_active.value_id > 0',
                'category_is_active.value',
                'category_is_active_default.value');

            $table = $isActive->getBackend()->getTable();
            $selectCategory
                ->joinLeft(
                    array('category_is_active' => $table),
                    'category_product_index.category_id = category_is_active.entity_id'
                    . ' AND category_is_active.store_id = category_product_index.store_id'
                    . $readAdapter->quoteInto(' AND category_is_active.attribute_id = ?', $isActive->getAttributeId()),
                    array())
                ->joinLeft(
                    array('category_is_active_default' => $table),
                    'category_product_index.category_id = category_is_active_default.entity_id'
                    . ' AND category_is_active_default.store_id = 0'
                    . ' AND ' . $readAdapter->quoteInto('category_is_active_default.attribute_id=?',
                        $isActive->getAttributeId()),
                    array())
                ->where("{$whereExpr} = 1");
        }

        $exprCatalogCategoryView = $readAdapter->getCheckSql(
            $readAdapter->quoteInto(
                'permission_index.grant_catalog_category_view = ?',
                Magento_CatalogPermissions_Model_Permission::PERMISSION_PARENT),
            'NULL',
            'permission_index.grant_catalog_category_view');

        $exprCatalogProductPrice = $readAdapter->getCheckSql(
            $readAdapter->quoteInto(
                'permission_index.grant_catalog_product_price = ?',
                Magento_CatalogPermissions_Model_Permission::PERMISSION_PARENT),
            'NULL',
            'permission_index.grant_catalog_product_price');

        $exprCheckoutItems = $readAdapter->getCheckSql(
            $readAdapter->quoteInto(
                'permission_index.grant_checkout_items = ?',
                Magento_CatalogPermissions_Model_Permission::PERMISSION_PARENT),
            'NULL',
            'permission_index.grant_checkout_items');

        $selectCategory
            ->join(
                array('store' => $this->getTable('core_store')),
                'category_product_index.store_id = store.store_id',
                array())
            ->group(array(
                'category_product_index.store_id',
                'category_product_index.product_id',
                'permission_index.customer_group_id'
            ))
            // Select for per category product index (without anchor category usage)
            ->columns('category_id', 'category_product_index')
            ->join(
                array('permission_index'=>$this->getTable('magento_catalogpermissions_index')),
                'category_product_index.category_id = permission_index.category_id'
                . ' AND store.website_id = permission_index.website_id',
                array(
                    'customer_group_id',
                    'grant_catalog_category_view' => 'MAX(' . $exprCatalogCategoryView . ')',
                    'grant_catalog_product_price' => 'MAX(' . $exprCatalogProductPrice . ')',
                    'grant_checkout_items'        => 'MAX(' . $exprCheckoutItems . ')'
                ))
            ->group('category_product_index.category_id')
            ->where('category_product_index.is_parent = ?', 1);

        $exprCatalogCategoryView = $readAdapter->getCheckSql(
            $readAdapter->quoteInto(
                'permission_index_product.grant_catalog_category_view = ?',
                Magento_CatalogPermissions_Model_Permission::PERMISSION_PARENT),
            'NULL',
            'permission_index_product.grant_catalog_category_view');

        $exprCatalogProductPrice = $readAdapter->getCheckSql(
            $readAdapter->quoteInto(
                'permission_index_product.grant_catalog_product_price = ?',
                Magento_CatalogPermissions_Model_Permission::PERMISSION_PARENT),
            'NULL',
            'permission_index_product.grant_catalog_product_price');

        $exprCheckoutItems = $readAdapter->getCheckSql(
            $readAdapter->quoteInto(
                'permission_index_product.grant_checkout_items = ?',
                Magento_CatalogPermissions_Model_Permission::PERMISSION_PARENT),
            'NULL',
            'permission_index_product.grant_checkout_items');

        // Select for per category product index (with anchor category)
        $selectAnchorCategory = $readAdapter->select();
        $selectAnchorCategory
            ->from(
                array('permission_index_product'=>$this->getTable('magento_catalogpermissions_index_product')),
                array('product_id','store_id'))
            ->join(
                array('category_product_index' => $this->getTable('catalog_category_product_index')),
                'permission_index_product.product_id = category_product_index.product_id',
                array('category_id'))
            ->join(
                array('category'=>$this->getTable('catalog_category_entity')),
                'category.entity_id = category_product_index.category_id',
                array())
            ->join(
                array('category_child'=>$this->getTable('catalog_category_entity')),
                $readAdapter->quoteIdentifier('category_child.path') . ' LIKE '
                . $readAdapter->getConcatSql(array(
                    $readAdapter->quoteIdentifier('category.path'),
                    $readAdapter->quote('/%')))
                . ' AND category_child.entity_id = permission_index_product.category_id',
                array())
            ->columns(
                array(
                    'customer_group_id',
                    'grant_catalog_category_view' => 'MAX(' . $exprCatalogCategoryView . ')',
                    'grant_catalog_product_price' => 'MAX(' . $exprCatalogProductPrice.')',
                    'grant_checkout_items'        => 'MAX(' . $exprCheckoutItems . ')'
                ),
                'permission_index_product')
            ->group(array(
                'permission_index_product.store_id',
                'permission_index_product.product_id',
                'permission_index_product.customer_group_id',
                'category_product_index.category_id'))
            ->where('category_product_index.is_parent = 0');


        if ($productIds !== null && !empty($productIds)) {
            if (!is_array($productIds)) {
                $productIds = array($productIds);
            }
            $selectCategory->where('category_product_index.product_id IN(?)', $productIds);
            $selectAnchorCategory->where('permission_index_product.product_id IN(?)', $productIds);
            $condition = array('product_id IN(?)' => $productIds);
        } else {
            $condition = '';
        }

        $fields = array(
            'product_id', 'store_id', 'category_id', 'customer_group_id',
            'grant_catalog_category_view', 'grant_catalog_product_price',
            'grant_checkout_items'
        );

        $writeAdapter->delete($this->getTable('magento_catalogpermissions_index_product'), $condition);
        $writeAdapter->query($selectCategory->insertFromSelect($this->getTable('magento_catalogpermissions_index_product'), $fields));
        $writeAdapter->query(
            $selectAnchorCategory->insertFromSelect($this->getTable('magento_catalogpermissions_index_product'), $fields)
        );

        $this->reindexProductsStandalone($productIds);

        return $this;
    }

    /**
     * Reindex products permissions for standalone mode
     *
     * @param array|string $productIds
     * @return Magento_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function reindexProductsStandalone($productIds = null)
    {
        $readAdapter  = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();
        $selectConfig = $readAdapter->select();

        //columns expression
        $colCtlgCtgrView = $this->_getConfigGrantDbExpr('grant_catalog_category_view', 'permission_index_product');
        $colCtlgPrdctPrc = $this->_getConfigGrantDbExpr('grant_catalog_product_price', 'permission_index_product');
        $colChcktItms    = $this->_getConfigGrantDbExpr('grant_checkout_items', 'permission_index_product');

        // Config depend index select
        $selectConfig
            ->from(
                array('category_product_index' => $this->getTable('catalog_category_product_index')),
                array())
            ->join(
                array('permission_index_product'=>$this->getTable('magento_catalogpermissions_index_product')),
                'permission_index_product.product_id = category_product_index.product_id'
                . ' AND permission_index_product.store_id = category_product_index.store_id'
                . ' AND permission_index_product.is_config = 0',
                array('product_id', 'store_id'))
            ->joinLeft(
                array('permission_idx_product_exists'=>$this->getTable('magento_catalogpermissions_index_product')),
                'permission_idx_product_exists.product_id = permission_index_product.product_id'
                . ' AND permission_idx_product_exists.store_id = permission_index_product.store_id'
                . ' AND permission_idx_product_exists.customer_group_id=permission_index_product.customer_group_id'
                . ' AND permission_idx_product_exists.category_id = category_product_index.category_id',
                array())
            ->columns('category_id')
            ->columns(array(
                    'customer_group_id',
                    'grant_catalog_category_view' => $colCtlgCtgrView,
                    'grant_catalog_product_price' => $colCtlgPrdctPrc,
                    'grant_checkout_items'        => $colChcktItms,
                    'is_config' => new Zend_Db_Expr('1')),
                'permission_index_product')
            ->group(array(
                'category_product_index.category_id',
                'permission_index_product.product_id',
                'permission_index_product.store_id',
                'permission_index_product.customer_group_id'))
            ->where('permission_idx_product_exists.category_id IS NULL');


        $exprCatalogCategoryView = $readAdapter->getCheckSql(
            $readAdapter->quoteInto(
                'permission_index_product.grant_catalog_category_view = ?',
                Magento_CatalogPermissions_Model_Permission::PERMISSION_PARENT),
            'NULL',
            'permission_index_product.grant_catalog_category_view');


        $exprCatalogProductPrice = $readAdapter->getCheckSql(
            $readAdapter->quoteInto(
                'permission_index_product.grant_catalog_product_price = ?',
                Magento_CatalogPermissions_Model_Permission::PERMISSION_PARENT),
            'NULL',
            'permission_index_product.grant_catalog_product_price');

        $exprCheckoutItems = $readAdapter->getCheckSql(
            $readAdapter->quoteInto(
                'permission_index_product.grant_checkout_items = ?',
                Magento_CatalogPermissions_Model_Permission::PERMISSION_PARENT),
            'NULL',
            'permission_index_product.grant_checkout_items');

        // Select for standalone product index
        $selectStandalone = $readAdapter->select();
        $selectStandalone
            ->from(array('permission_index_product'=>$this->getTable('magento_catalogpermissions_index_product')),
                array(
                    'product_id',
                    'store_id'
                )
            )->columns(
                array(
                    'category_id' => new Zend_Db_Expr('NULL'),
                    'customer_group_id',
                    'grant_catalog_category_view' => 'MAX(' . $exprCatalogCategoryView . ')',
                    'grant_catalog_product_price' => 'MAX(' . $exprCatalogProductPrice . ')',
                    'grant_checkout_items'        => 'MAX(' . $exprCheckoutItems . ')',
                    'is_config' => new Zend_Db_Expr('1')
                ),
                'permission_index_product'
            )->group(array(
                'permission_index_product.store_id',
                'permission_index_product.product_id',
                'permission_index_product.customer_group_id'
            ));

        $condition = array('is_config = 1');



        if ($productIds !== null && !empty($productIds)) {
            if (!is_array($productIds)) {
                $productIds = array($productIds);
            }
            $selectConfig->where('category_product_index.product_id IN(?)', $productIds);
            $selectStandalone->where('permission_index_product.product_id IN(?)', $productIds);
            $condition['product_id IN(?)'] = $productIds;
        }

        $fields = array(
            'product_id', 'store_id', 'category_id', 'customer_group_id',
            'grant_catalog_category_view', 'grant_catalog_product_price',
            'grant_checkout_items', 'is_config'
        );

        $writeAdapter->delete($this->getTable('magento_catalogpermissions_index_product'), $condition);
        $writeAdapter->query($selectConfig->insertFromSelect($this->getTable('magento_catalogpermissions_index_product'), $fields));
        $writeAdapter->query($selectStandalone->insertFromSelect($this->getTable('magento_catalogpermissions_index_product'), $fields));
        // Fix inherited permissions
        $deny = (int) Magento_CatalogPermissions_Model_Permission::PERMISSION_DENY;

        $data = array(
            'grant_catalog_product_price' => $readAdapter->getCheckSql(
                $readAdapter->quoteInto('grant_catalog_category_view = ?', $deny),
                $deny,
                'grant_catalog_product_price'
            ),
            'grant_checkout_items' => $readAdapter->getCheckSql(
                $readAdapter->quoteInto('grant_catalog_category_view = ?', $deny)
                    . ' OR ' . $readAdapter->quoteInto('grant_catalog_product_price = ?', $deny),
                $deny,
                'grant_checkout_items'
            )
        );
        $writeAdapter->update($this->getTable('magento_catalogpermissions_index_product'), $data, $condition);

        return $this;
    }

    /**
     * Generates CASE ... WHEN .... THEN expression for grant depends on config
     *
     * @param string $grant
     * @param string $tableAlias
     * @return Zend_Db_Expr
     */
    protected function _getConfigGrantDbExpr($grant, $tableAlias)
    {
        $result      = new Zend_Db_Expr('0');
        $conditions  = array();
        $readAdapter = $this->_getReadAdapter();

        foreach ($this->_getStoreIds() as $storeId) {
            $config = Mage::getStoreConfig(self::XML_PATH_GRANT_BASE . $grant);

            if ($config == 2) {
                $groups = explode(',', trim(Mage::getStoreConfig(
                    self::XML_PATH_GRANT_BASE . $grant . '_groups'
                )));

                foreach ($groups as $groupId) {
                    if (is_numeric($groupId)) {
                        // Case per customer group
                        $condition = $readAdapter->quoteInto($tableAlias . '.store_id = ?', $storeId)
                        . ' AND ' . $readAdapter->quoteInto($tableAlias . '.customer_group_id = ?', (int) $groupId);
                        $conditions[$condition] = Magento_CatalogPermissions_Model_Permission::PERMISSION_ALLOW;
                    }
                }

                $condition = $readAdapter->quoteInto($tableAlias . '.store_id = ?', $storeId);
                $conditions[$condition] = Magento_CatalogPermissions_Model_Permission::PERMISSION_DENY;
            } else {
                $condition = $readAdapter->quoteInto($tableAlias . '.store_id = ?', $storeId);
                $conditions[$condition] = (
                    $config ?
                    Magento_CatalogPermissions_Model_Permission::PERMISSION_ALLOW :
                    Magento_CatalogPermissions_Model_Permission::PERMISSION_DENY
                );
            }
        }

        if (!empty($conditions)) {
            $expr = 'CASE ';
            foreach ($conditions as $condition => $value) {
                $expr .= ' WHEN ' . $condition . ' THEN ' . $this->_getReadAdapter()->quote($value);
            }
            $expr .= ' END';
            $result = new Zend_Db_Expr($expr);
        }

        return $result;
    }

    /**
     * Retrieve store ids
     *
     * @return array
     */
    protected function _getStoreIds()
    {
        if (empty($this->_storeIds)) {
            $this->_storeIds = array();
            /** @var $store Magento_Core_Model_Store */
            foreach ($this->_storeManager->getStores(true) as $store) {
                $this->_storeIds[] = (int)$store->getId();
            }
        }

        return $this->_storeIds;
    }

    /**
     * Inherit category permission from it's parent
     *
     * @param string $path
     * @return Magento_CatalogPermissions_Model_Resource_Permission_Index
     */
    protected function _inheritCategoryPermission($path)
    {
        if (strpos($path, '/') !== false) {
            $parentPath = substr($path, 0, strrpos($path, '/'));
        } else {
            $parentPath = '';
        }

        $permissionParent = Magento_CatalogPermissions_Model_Permission::PERMISSION_PARENT;

        if (isset($this->_permissionCache[$path])) {
            foreach (array_keys($this->_permissionCache[$path]) as $uniqKey) {
                if (isset($this->_permissionCache[$parentPath][$uniqKey])) {
                    foreach ($this->_grantsInheritance as $grant => $inheritance) {

                        $value = $this->_permissionCache[$parentPath][$uniqKey][$grant];

                        if ($this->_permissionCache[$path][$uniqKey][$grant] == $permissionParent) {
                            $this->_permissionCache[$path][$uniqKey][$grant] = $value;
                        } else {
                            if ($inheritance == 'allow') {
                                $value = max(
                                    $this->_permissionCache[$path][$uniqKey][$grant],
                                    $value
                                );
                            }

                            $value = min(
                                $this->_permissionCache[$path][$uniqKey][$grant],
                                $value
                            );

                            $this->_permissionCache[$path][$uniqKey][$grant] = $value;
                        }

                        if ($this->_permissionCache[$path][$uniqKey][$grant] == $permissionParent) {
                            $this->_permissionCache[$path][$uniqKey][$grant] = null;
                        }

                    }
                }
            }
            if (isset($this->_permissionCache[$parentPath])) {
                foreach (array_keys($this->_permissionCache[$parentPath]) as $uniqKey) {
                    if (!isset($this->_permissionCache[$path][$uniqKey])) {
                        $this->_permissionCache[$path][$uniqKey] = $this->_permissionCache[$parentPath][$uniqKey];
                    }
                }
            }
        } elseif (isset($this->_permissionCache[$parentPath])) {
            $this->_permissionCache[$path] = $this->_permissionCache[$parentPath];
        }



        return $this;
    }

    /**
     * Retrieve permission index for category or categories with specified customer group and website id
     *
     * @param int|array $categoryId
     * @param int $customerGroupId
     * @param int $websiteId
     * @return array
     */
    public function getIndexForCategory($categoryId, $customerGroupId = null, $websiteId = null)
    {
        $adapter = $this->_getReadAdapter();
        if (!is_array($categoryId)) {
            $categoryId = array($categoryId);
        }

        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where('category_id IN(?)', $categoryId);
        if (!is_null($customerGroupId)) {
            $select->where('customer_group_id = ?', $customerGroupId);
        }
        if (!is_null($websiteId)) {
            $select->where('website_id = ?', $websiteId);
        }

        return $adapter->fetchAssoc($select);
    }

    /**
     * Retrieve restricted category ids for customer group and website
     *
     * @param int $customerGroupId
     * @param int $websiteId
     * @return array
     */
    public function getRestrictedCategoryIds($customerGroupId, $websiteId)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getMainTable(), 'category_id')
            ->where('grant_catalog_category_view = :grant_catalog_category_view');
        $bind = array();
        if ($customerGroupId) {
            $select->where('customer_group_id = :customer_group_id');
            $bind[':customer_group_id'] = $customerGroupId;
        }
        if ($websiteId) {
            $select->where('website_id = :website_id');
            $bind[':website_id'] = $websiteId;
        }
        if (!Mage::helper('Magento_CatalogPermissions_Helper_Data')->isAllowedCategoryView()) {
            $bind[':grant_catalog_category_view'] = Magento_CatalogPermissions_Model_Permission::PERMISSION_ALLOW;
        } else {
            $bind[':grant_catalog_category_view'] = Magento_CatalogPermissions_Model_Permission::PERMISSION_DENY;
        }

        $restrictedCatIds = $adapter->fetchCol($select, $bind);

        $select = $adapter->select()
            ->from($this->getTable('catalog_category_entity'), 'entity_id');

        if (!empty($restrictedCatIds) && !Mage::helper('Magento_CatalogPermissions_Helper_Data')->isAllowedCategoryView()) {
            $select->where('entity_id NOT IN(?)', $restrictedCatIds);
        } elseif (!empty($restrictedCatIds) && Mage::helper('Magento_CatalogPermissions_Helper_Data')->isAllowedCategoryView()) {
            $select->where('entity_id IN(?)', $restrictedCatIds);
        } elseif (Mage::helper('Magento_CatalogPermissions_Helper_Data')->isAllowedCategoryView()) {
            $select->where('1 = 0'); // category view allowed for all
        }

        return $adapter->fetchCol($select);
    }

    /**
     * Apply price grant on price index select
     *
     * @param Magento_Object $data
     * @param int $customerGroupId
     * @return Magento_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function applyPriceGrantToPriceIndex($data, $customerGroupId)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $data->getSelect();
        $parts   = $select->getPart(Zend_Db_Select::FROM);

        if (!isset($parts['permission_index_product'])) {
            $select->joinLeft(
                array('permission_index_product'=>$this->getTable('magento_catalogpermissions_index_product')),
                'permission_index_product.category_id IS NULL'
                . ' AND permission_index_product.product_id = ' . $data->getTable() .'.entity_id'
                . ' AND ' . $adapter->quoteInto('permission_index_product.store_id = ?', $data->getStoreId())
                . ' AND ' . $adapter->quoteInto('permission_index_product.customer_group_id = ?', $customerGroupId),
                array()
            );
        }

        if (!Mage::helper('Magento_CatalogPermissions_Helper_Data')->isAllowedProductPrice()) {
            $select->where(
                'permission_index_product.grant_catalog_product_price = ?',
                Magento_CatalogPermissions_Model_Permission::PERMISSION_ALLOW);
        } else {
            $select->where(
                'permission_index_product.grant_catalog_product_price != ?'
                . ' OR permission_index_product.grant_catalog_product_price IS NULL',
                Magento_CatalogPermissions_Model_Permission::PERMISSION_DENY);
        }

        return $this;
    }

    /**
     * Add index to product count select in product collection
     *
     * @param Magento_Catalog_Model_Resource_Product_Collection $collection
     * @param int $customerGroupId
     * @return Magento_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function addIndexToProductCount($collection, $customerGroupId)
    {
        $adapter = $this->_getReadAdapter();
        $parts = $collection->getSelect()->getPart(Zend_Db_Select::FROM);

        if (isset($parts['permission_index_product'])) {
            return $this;
        }

        $collection->getProductCountSelect()
            ->joinLeft(
                array('permission_index_product_count'=>$this->getTable('magento_catalogpermissions_index_product')),
                'permission_index_product_count.category_id = count_table.category_id'
                . ' AND permission_index_product_count.product_id = count_table.product_id'
                . ' AND permission_index_product_count.store_id = count_table.store_id'
                . ' AND ' . $adapter->quoteInto('permission_index_product_count.customer_group_id=?', $customerGroupId),
                array()
            );

        if (!Mage::helper('Magento_CatalogPermissions_Helper_Data')->isAllowedCategoryView()) {
            $collection->getProductCountSelect()
                ->where('permission_index_product_count.grant_catalog_category_view = ?',
                    Magento_CatalogPermissions_Model_Permission::PERMISSION_ALLOW);
        } else {
            $collection->getProductCountSelect()
                ->where('permission_index_product_count.grant_catalog_category_view != ?'
                    . ' OR permission_index_product_count.grant_catalog_category_view IS NULL',
                    Magento_CatalogPermissions_Model_Permission::PERMISSION_DENY);
        }

        return $this;
    }

    /**
     * Add index to category collection
     *
     * @param Magento_Catalog_Model_Resource_Category_Collection|Magento_Catalog_Model_Resource_Category_Flat_Collection $collection
     * @param int $customerGroupId
     * @param int $websiteId
     * @return Magento_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function addIndexToCategoryCollection($collection, $customerGroupId, $websiteId)
    {
        $adapter = $this->_getReadAdapter();
        if ($collection instanceof Magento_Catalog_Model_Resource_Category_Flat_Collection) {
            $tableAlias = 'main_table';
        } else {
            $tableAlias = 'e';
        }

        $collection->getSelect()->joinLeft(
            array('permission_index'=>$this->getTable('magento_catalogpermissions_index')),
            'permission_index.category_id = ' . $tableAlias . '.entity_id'
            . ' AND ' . $adapter->quoteInto('permission_index.website_id = ?', $websiteId)
            . ' AND ' . $adapter->quoteInto('permission_index.customer_group_id = ?', $customerGroupId),
            array()
        );

        if (!Mage::helper('Magento_CatalogPermissions_Helper_Data')->isAllowedCategoryView()) {
            $collection->getSelect()
                ->where('permission_index.grant_catalog_category_view = ?',
                    Magento_CatalogPermissions_Model_Permission::PERMISSION_ALLOW);
        } else {
            $collection->getSelect()
                ->where('permission_index.grant_catalog_category_view != ?'
                    . ' OR permission_index.grant_catalog_category_view IS NULL',
                    Magento_CatalogPermissions_Model_Permission::PERMISSION_DENY);
        }

        return $this;
    }

    /**
     * Add index select in product collection
     *
     * @param Magento_Catalog_Model_Resource_Product_Collection $collection
     * @param int $customerGroupId
     * @return Magento_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function addIndexToProductCollection($collection, $customerGroupId)
    {
        $adapter = $this->_getReadAdapter();
        $parts = $collection->getSelect()->getPart(Zend_Db_Select::FROM);

        $conditions = array();
        if (isset($parts['cat_index'])
            && $parts['cat_index']['tableName'] == $this->getTable('catalog_category_product_index')
        ) {
            $conditions[] = 'permission_index_product.category_id = cat_index.category_id';
            $conditions[] = 'permission_index_product.product_id = cat_index.product_id';
            $conditions[] = 'permission_index_product.store_id = cat_index.store_id';
        } else {
            $conditions[] = 'permission_index_product.category_id IS NULL';
            $conditions[] = 'permission_index_product.product_id = e.entity_id';
            $conditions[] = $adapter->quoteInto('permission_index_product.store_id = ?', $collection->getStoreId());
        }
        $conditions[] = $adapter->quoteInto('permission_index_product.customer_group_id = ?', $customerGroupId);

        $condition = join(' AND ', $conditions);

        if (isset($parts['permission_index_product'])) {
            $parts['permission_index_product']['joinCondition'] = $condition;
            $collection->getSelect()->setPart(Zend_Db_Select::FROM, $parts);
        } else {
            $collection->getSelect()
                ->joinLeft(
                    array('permission_index_product' => $this->getTable('magento_catalogpermissions_index_product')),
                    $condition,
                    array('grant_catalog_category_view',
                        'grant_catalog_product_price',
                        'grant_checkout_items')
                );
            if (!Mage::helper('Magento_CatalogPermissions_Helper_Data')->isAllowedCategoryView()) {
                $collection->getSelect()
                    ->where('permission_index_product.grant_catalog_category_view = ?',
                        Magento_CatalogPermissions_Model_Permission::PERMISSION_ALLOW);
            } else {
                $collection->getSelect()
                    ->where('permission_index_product.grant_catalog_category_view != ?'
                        . ' OR permission_index_product.grant_catalog_category_view IS NULL',
                        Magento_CatalogPermissions_Model_Permission::PERMISSION_DENY);
            }

            /*
             * Checking if passed collection has link model attached
             */
            if (method_exists($collection, 'getLinkModel')) {
                $linkTypeId = $collection->getLinkModel()->getLinkTypeId();
                $linkTypeIds = array(
                    Magento_Catalog_Model_Product_Link::LINK_TYPE_CROSSSELL,
                    Magento_Catalog_Model_Product_Link::LINK_TYPE_UPSELL
                );

                /*
                 * If collection has appropriate link type (cross-sell or up-sell) we need to
                 * limit products by permissions (display price and add to cart)
                 */
                if (in_array($linkTypeId, $linkTypeIds)) {

                    if (!Mage::helper('Magento_CatalogPermissions_Helper_Data')->isAllowedProductPrice()) {
                        $collection->getSelect()
                            ->where('permission_index_product.grant_catalog_product_price = ?',
                                Magento_CatalogPermissions_Model_Permission::PERMISSION_ALLOW);
                    } else {
                        $collection->getSelect()
                            ->where('permission_index_product.grant_catalog_product_price != ?'
                                . ' OR permission_index_product.grant_catalog_product_price IS NULL',
                                Magento_CatalogPermissions_Model_Permission::PERMISSION_DENY);
                    }

                    if (!Mage::helper('Magento_CatalogPermissions_Helper_Data')->isAllowedCheckoutItems()) {
                        $collection->getSelect()
                            ->where('permission_index_product.grant_checkout_items = ?',
                                Magento_CatalogPermissions_Model_Permission::PERMISSION_ALLOW);
                    } else {
                        $collection->getSelect()
                            ->where('permission_index_product.grant_checkout_items != ?'
                                . ' OR permission_index_product.grant_checkout_items IS NULL',
                                Magento_CatalogPermissions_Model_Permission::PERMISSION_DENY);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Add permission index to product model
     *
     * @param Magento_Catalog_Model_Product $product
     * @param int $customerGroupId
     * @return Magento_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function addIndexToProduct($product, $customerGroupId)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getTable('magento_catalogpermissions_index_product'),
                array(
                    'grant_catalog_category_view',
                    'grant_catalog_product_price',
                    'grant_checkout_items'
                )
            )
            ->where('product_id = :product_id')
            ->where('customer_group_id = :customer_group_id')
            ->where('store_id = :store_id');
        $bind = array(
            ':product_id'        => $product->getId(),
            ':customer_group_id' => $customerGroupId,
            ':store_id'          => $product->getStoreId()
        );
        if ($product->getCategory()) {
            $select->where('category_id = :category_id');
            $bind[':category_id'] = $product->getCategory()->getId();
        } else {
            $select->where('category_id IS NULL');
        }

        $permission = $adapter->fetchRow($select, $bind);

        if ($permission) {
            $product->addData($permission);
        }

        return $this;
    }

    /**
     * Get permission index for products
     *
     * @param int|array $productId
     * @param int $customerGroupId
     * @param int $storeId
     * @return array
     */
    public function getIndexForProduct($productId, $customerGroupId, $storeId)
    {
        $adapter = $this->_getReadAdapter();
        if (!is_array($productId)) {
            $productId = array($productId);
        }

        $select = $adapter->select()
            ->from($this->getTable('magento_catalogpermissions_index_product'),
                array(
                    'product_id',
                    'grant_catalog_category_view',
                    'grant_catalog_product_price',
                    'grant_checkout_items'
                )
            )
            ->where('product_id IN(?)', $productId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('store_id = ?', $storeId)
            ->where('category_id IS NULL');

        return $adapter->fetchAssoc($select);
    }

    /**
     * Prepare base information for data insert
     *
     * @param string $table
     * @param array $fields
     * @return Magento_CatalogPermissions_Model_Resource_Permission_Index
     */
    protected function _beginInsert($table, $fields)
    {
        $this->_tableFields[$table] = $fields;
        return $this;
    }

    /**
     * Put data into table
     *
     * @param string $table
     * @param bool $forced
     * @return Magento_CatalogPermissions_Model_Resource_Permission_Index
     */
    protected function _commitInsert($table, $forced = true)
    {
        $readAdapter  = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();
        if (isset($this->_insertData[$table]) && count($this->_insertData[$table])
            && ($forced || count($this->_insertData[$table]) >= 100)
        ) {

            $writeAdapter->insertMultiple($this->getTable($table), $this->_insertData[$table]);

            $this->_insertData[$table] = array();
        }
        return $this;
    }

    /**
     * Insert data to table
     *
     * @param string $table
     * @param array $data
     * @return Magento_CatalogPermissions_Model_Resource_Permission_Index
     */
    protected function _insert($table, $data)
    {
        $this->_insertData[$table][] = $data;
        $this->_commitInsert($table, false);
        return $this;
    }

    /**
     * Reindex all
     *
     * @return Magento_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function reindexAll()
    {
        $this->beginTransaction();
        try {
            $this->reindex();
            $this->reindexProducts();
            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
        return $this;
    }
}
