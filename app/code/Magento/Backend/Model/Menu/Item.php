<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Backend\Model\Menu;

/**
 * Menu item. Should be used to create nested menu structures with \Magento\Backend\Model\Menu
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Item
{
    /**
     * Menu item id
     *
     * @var string
     */
    protected $_id;

    /**
     * Menu item title
     *
     * @var string
     */
    protected $_title;

    /**
     * Module of menu item
     *
     * @var string
     */
    protected $_moduleName;

    /**
     * Menu item sort index in list
     *
     * @var string
     */
    protected $_sortIndex = null;

    /**
     * Menu item action
     *
     * @var string
     */
    protected $_action = null;

    /**
     * Parent menu item id
     *
     * @var string
     */
    protected $_parentId = null;

    /**
     * Acl resource of menu item
     *
     * @var string
     */
    protected $_resource;

    /**
     * Item tooltip text
     *
     * @var string
     */
    protected $_tooltip;

    /**
     * Path from root element in tree
     *
     * @var string
     */
    protected $_path = '';

    /**
     * Acl
     *
     * @var \Magento\AuthorizationInterface
     */
    protected $_acl;

    /**
     * Module that item is dependent on
     *
     * @var string|null
     */
    protected $_dependsOnModule;

    /**
     * Global config option that item is dependent on
     *
     * @var string|null
     */
    protected $_dependsOnConfig;

    /**
     * Submenu item list
     *
     * @var \Magento\Backend\Model\Menu
     */
    protected $_submenu;

    /**
     * @var \Magento\Backend\Model\MenuFactory
     */
    protected $_menuFactory;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_urlModel;

    /**
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_storeConfig;

    /**
     * @var \Magento\Backend\Model\Menu\Item\Validator
     */
    protected $_validator;

    /**
     * Serialized submenu string
     *
     * @var string
     */
    protected $_serializedSubmenu;

    /**
     * Module list
     *
     * @var \Magento\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var \Magento\Module\Manager
     */
    private $_moduleManager;

    /**
     * @param Item\Validator $validator
     * @param \Magento\AuthorizationInterface $authorization
     * @param \Magento\Core\Model\Store\Config $storeConfig
     * @param \Magento\Backend\Model\MenuFactory $menuFactory
     * @param \Magento\Backend\Model\Url $urlModel
     * @param \Magento\Module\ModuleListInterface $moduleList
     * @param \Magento\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Model\Menu\Item\Validator $validator,
        \Magento\AuthorizationInterface $authorization,
        \Magento\Core\Model\Store\Config $storeConfig,
        \Magento\Backend\Model\MenuFactory $menuFactory,
        \Magento\Backend\Model\Url $urlModel,
        \Magento\Module\ModuleListInterface $moduleList,
        \Magento\Module\Manager $moduleManager,
        array $data = array()
    ) {
        $this->_validator = $validator;
        $this->_validator->validate($data);

        $this->_moduleManager = $moduleManager;
        $this->_acl = $authorization;
        $this->_storeConfig = $storeConfig;
        $this->_menuFactory = $menuFactory;
        $this->_urlModel = $urlModel;
        $this->_moduleName = isset($data['module']) ? $data['module'] : 'Magento_Backend';
        $this->_moduleList = $moduleList;

        $this->_id = $data['id'];
        $this->_title = $data['title'];
        $this->_action = $this->_getArgument($data, 'action');
        $this->_resource = $this->_getArgument($data, 'resource');
        $this->_dependsOnModule = $this->_getArgument($data, 'dependsOnModule');
        $this->_dependsOnConfig = $this->_getArgument($data, 'dependsOnConfig');
        $this->_tooltip = $this->_getArgument($data, 'toolTip', '');
    }

    /**
     * Retrieve argument element, or default value
     *
     * @param array $array
     * @param mixed $key
     * @param mixed $defaultValue
     * @return mixed
     */
    protected function _getArgument(array $array, $key, $defaultValue = null)
    {
        return isset($array[$key]) ? $array[$key] : $defaultValue;
    }

    /**
     * Retrieve item id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Check whether item has subnodes
     *
     * @return bool
     */
    public function hasChildren()
    {
        return !is_null($this->_submenu) && (bool) $this->_submenu->count();
    }

    /**
     * Retrieve submenu
     *
     * @return \Magento\Backend\Model\Menu
     */
    public function getChildren()
    {
        if (!$this->_submenu) {
            $this->_submenu = $this->_menuFactory->create();
        }
        return $this->_submenu;
    }

    /**
     * Retrieve menu item url
     *
     * @return string
     */
    public function getUrl()
    {
        if ((bool) $this->_action) {
            return $this->_urlModel->getUrl((string)$this->_action, array('_cache_secret_key' => true));
        }
        return '#';
    }

    /**
     * Retrieve menu item action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * Set Item action
     *
     * @param string $action
     * @return \Magento\Backend\Model\Menu\Item
     * @throws \InvalidArgumentException
     */
    public function setAction($action)
    {
        $this->_validator->validateParam('action', $action);
        $this->_action = $action;
        return $this;
    }

    /**
     * Check whether item has javascript callback on click
     *
     * @return bool
     */
    public function hasClickCallback()
    {
        return $this->getUrl() == '#';
    }

    /**
     * Retrieve item click callback
     *
     * @return string
     */
    public function getClickCallback()
    {
        if ($this->getUrl() == '#') {
            return 'return false;';
        }
        return '';
    }

    /**
     * Retrieve tooltip text title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Set Item title
     *
     * @param string $title
     * @return \Magento\Backend\Model\Menu\Item
     * @throws \InvalidArgumentException
     */
    public function setTitle($title)
    {
        $this->_validator->validateParam('title', $title);
        $this->_title = $title;
        return $this;
    }

    /**
     * Check whether item has tooltip text
     *
     * @return bool
     */
    public function hasTooltip()
    {
        return (bool) $this->_tooltip;
    }

    /**
     * Retrieve item tooltip text
     *
     * @return string
     */
    public function getTooltip()
    {
        return $this->_tooltip;
    }

    /**
     * Set Item tooltip
     *
     * @param string $tooltip
     * @return \Magento\Backend\Model\Menu\Item
     * @throws \InvalidArgumentException
     */
    public function setTooltip($tooltip)
    {
        $this->_validator->validateParam('toolTip', $tooltip);
        $this->_tooltip = $tooltip;
        return $this;
    }

    /**
     * Set Item module
     *
     * @param string $module
     * @return \Magento\Backend\Model\Menu\Item
     * @throws \InvalidArgumentException
     */
    public function setModule($module)
    {
        $this->_validator->validateParam('module', $module);
        $this->_moduleName = $module;
        return $this;
    }

    /**
     * Set Item module dependency
     *
     * @param string $moduleName
     * @return \Magento\Backend\Model\Menu\Item
     * @throws \InvalidArgumentException
     */
    public function setModuleDependency($moduleName)
    {
        $this->_validator->validateParam('dependsOnModule', $moduleName);
        $this->_dependsOnModule = $moduleName;
        return $this;
    }

    /**
     * Set Item config dependency
     *
     * @param string $configPath
     * @return \Magento\Backend\Model\Menu\Item
     * @throws \InvalidArgumentException
     */
    public function setConfigDependency($configPath)
    {
        $this->_validator->validateParam('dependsOnConfig', $configPath);
        $this->_dependsOnConfig = $configPath;
        return $this;
    }

    /**
     * Check whether item is disabled. Disabled items are not shown to user
     *
     * @return bool
     */
    public function isDisabled()
    {
        return !$this->_moduleManager->isOutputEnabled($this->_moduleName)
            || !$this->_isModuleDependenciesAvailable()
            || !$this->_isConfigDependenciesAvailable();
    }

    /**
     * Check whether module that item depends on is active
     *
     * @return bool
     */
    protected function _isModuleDependenciesAvailable()
    {
        if ($this->_dependsOnModule) {
            $module = $this->_dependsOnModule;
            return !!$this->_moduleList->getModule($module);
        }
        return true;
    }

    /**
     * Check whether config dependency is available
     *
     * @return bool
     */
    protected function _isConfigDependenciesAvailable()
    {
        if ($this->_dependsOnConfig) {
            return $this->_storeConfig->getConfigFlag((string)$this->_dependsOnConfig);
        }
        return true;
    }

    /**
     * Check whether item is allowed to the user
     *
     * @return bool
     */
    public function isAllowed()
    {
        try {
            return $this->_acl->isAllowed((string)$this->_resource);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function __sleep()
    {
        if ($this->_submenu) {
            $this->_serializedSubmenu = $this->_submenu->serialize();
        }
        return array(
            '_parentId',
            '_moduleName',
            '_sortIndex',
            '_dependsOnConfig',
            '_id',
            '_resource',
            '_path',
            '_action',
            '_dependsOnModule',
            '_tooltip',
            '_title',
            '_serializedSubmenu'
        );
    }

    public function __wakeup()
    {
        $objectManager = \Magento\App\ObjectManager::getInstance();
        $this->_moduleManager = $objectManager->get('Magento\Module\Manager');
        $this->_validator = $objectManager->get('Magento\Backend\Model\Menu\Item\Validator');
        $this->_acl = $objectManager->get('Magento\AuthorizationInterface');
        $this->_storeConfig = $objectManager->get('Magento\Core\Model\Store\Config');
        $this->_menuFactory = $objectManager->get('Magento\Backend\Model\MenuFactory');
        $this->_urlModel = $objectManager->get('Magento\Backend\Model\Url');
        $this->_moduleList = $objectManager->get('Magento\Module\ModuleListInterface');
        if ($this->_serializedSubmenu) {
            $this->_submenu = $this->_menuFactory->create();
            $this->_submenu->unserialize($this->_serializedSubmenu);
        }
    }
}
