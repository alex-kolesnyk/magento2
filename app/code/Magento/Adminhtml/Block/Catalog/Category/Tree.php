<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Categories tree block
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Magento_Adminhtml_Block_Catalog_Category_Tree extends Magento_Adminhtml_Block_Catalog_Category_Abstract
{
    protected $_withProductCount;

    protected $_template = 'catalog/category/tree.phtml';

    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(true);
        $this->_withProductCount = true;
    }

    protected function _prepareLayout()
    {
        $addUrl = $this->getUrl("*/*/add", array(
            '_current'=>true,
            'id'=>null,
            '_query' => false
        ));

        $this->addChild('add_sub_button', 'Magento_Adminhtml_Block_Widget_Button', array(
            'label'     => __('Add Subcategory'),
            'onclick'   => "addNew('".$addUrl."', false)",
            'class'     => 'add',
            'id'            => 'add_subcategory_button',
            'style'     => $this->canAddSubCategory() ? '' : 'display: none;'
        ));

        if ($this->canAddRootCategory()) {
            $this->addChild('add_root_button', 'Magento_Adminhtml_Block_Widget_Button', array(
                'label'     => __('Add Root Category'),
                'onclick'   => "addNew('".$addUrl."', true)",
                'class'     => 'add',
                'id'        => 'add_root_category_button'
            ));
        }

        $this->setChild('store_switcher',
            $this->getLayout()->createBlock('Magento_Backend_Block_Store_Switcher')
                ->setSwitchUrl($this->getUrl('*/*/*', array('_current'=>true, '_query'=>false, 'store'=>null)))
                ->setTemplate('store/switcher/enhanced.phtml')
        );
        return parent::_prepareLayout();
    }

    protected function _getDefaultStoreId()
    {
        return Magento_Catalog_Model_Abstract::DEFAULT_STORE_ID;
    }

    public function getCategoryCollection()
    {
        $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
        $collection = $this->getData('category_collection');
        if (is_null($collection)) {
            $collection = Mage::getModel('Magento_Catalog_Model_Category')->getCollection();

            /* @var $collection Magento_Catalog_Model_Resource_Category_Collection */
            $collection->addAttributeToSelect('name')
                ->addAttributeToSelect('is_active')
                ->setProductStoreId($storeId)
                ->setLoadProductCount($this->_withProductCount)
                ->setStoreId($storeId);

            $this->setData('category_collection', $collection);
        }
        return $collection;
    }

    /**
     * Retrieve list of categories with name containing $namePart and their parents
     *
     * @param string $namePart
     * @return string
     */
    public function getSuggestedCategoriesJson($namePart)
    {
        $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());

        /* @var $collection Magento_Catalog_Model_Resource_Category_Collection */
        $collection = Mage::getModel('Magento_Catalog_Model_Category')->getCollection();

        $matchingNamesCollection = clone $collection;
        $escapedNamePart = Mage::getResourceHelper('Magento_Core')->addLikeEscape($namePart, array('position' => 'any'));
        $matchingNamesCollection->addAttributeToFilter('name', array('like' => $escapedNamePart))
            ->addAttributeToFilter('entity_id', array('neq' => Magento_Catalog_Model_Category::TREE_ROOT_ID))
            ->addAttributeToSelect('path')
            ->setStoreId($storeId);

        $shownCategoriesIds = array();
        foreach ($matchingNamesCollection as $category) {
            foreach (explode('/', $category->getPath()) as $parentId) {
                $shownCategoriesIds[$parentId] = 1;
            }
        }

        $collection->addAttributeToFilter('entity_id', array('in' => array_keys($shownCategoriesIds)))
            ->addAttributeToSelect(array('name', 'is_active', 'parent_id'))
            ->setStoreId($storeId);

        $categoryById = array(
            Magento_Catalog_Model_Category::TREE_ROOT_ID => array(
                'id' => Magento_Catalog_Model_Category::TREE_ROOT_ID,
                'children' => array()
            )
        );
        foreach ($collection as $category) {
            foreach (array($category->getId(), $category->getParentId()) as $categoryId) {
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = array('id' => $categoryId, 'children' => array());
                }
            }
            $categoryById[$category->getId()]['is_active'] = $category->getIsActive();
            $categoryById[$category->getId()]['label'] = $category->getName();
            $categoryById[$category->getParentId()]['children'][] = &$categoryById[$category->getId()];
        }

        return $this->_coreData->jsonEncode(
            $categoryById[Magento_Catalog_Model_Category::TREE_ROOT_ID]['children']
        );
    }

    public function getAddRootButtonHtml()
    {
        return $this->getChildHtml('add_root_button');
    }

    public function getAddSubButtonHtml()
    {
        return $this->getChildHtml('add_sub_button');
    }

    public function getExpandButtonHtml()
    {
        return $this->getChildHtml('expand_button');
    }

    public function getCollapseButtonHtml()
    {
        return $this->getChildHtml('collapse_button');
    }

    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }

    public function getLoadTreeUrl($expanded=null)
    {
        $params = array('_current'=>true, 'id'=>null,'store'=>null);
        if (
            (is_null($expanded) && Mage::getSingleton('Magento_Backend_Model_Auth_Session')->getIsTreeWasExpanded())
            || $expanded == true) {
            $params['expand_all'] = true;
        }
        return $this->getUrl('*/*/categoriesJson', $params);
    }

    public function getNodesUrl()
    {
        return $this->getUrl('*/catalog_category/jsonTree');
    }

    public function getSwitchTreeUrl()
    {
        return $this->getUrl(
            "*/catalog_category/tree",
            array('_current'=>true, 'store'=>null, '_query'=>false, 'id'=>null, 'parent'=>null)
        );
    }

    public function getIsWasExpanded()
    {
        return Mage::getSingleton('Magento_Backend_Model_Auth_Session')->getIsTreeWasExpanded();
    }

    public function getMoveUrl()
    {
        return $this->getUrl('*/catalog_category/move', array('store'=>$this->getRequest()->getParam('store')));
    }

    public function getTree($parenNodeCategory=null)
    {
           $rootArray = $this->_getNodeJson($this->getRoot($parenNodeCategory));
        $tree = isset($rootArray['children']) ? $rootArray['children'] : array();
        return $tree;
    }

    public function getTreeJson($parenNodeCategory=null)
    {
        $rootArray = $this->_getNodeJson($this->getRoot($parenNodeCategory));
        $json = $this->_coreData->jsonEncode(
            isset($rootArray['children']) ? $rootArray['children'] : array()
        );
        return $json;
    }

    /**
     * Get JSON of array of categories, that are breadcrumbs for specified category path
     *
     * @param string $path
     * @param string $javascriptVarName
     * @return string
     */
    public function getBreadcrumbsJavascript($path, $javascriptVarName)
    {
        if (empty($path)) {
            return '';
        }

        $categories = Mage::getResourceSingleton('Magento_Catalog_Model_Resource_Category_Tree')
            ->setStoreId($this->getStore()->getId())->loadBreadcrumbsArray($path);
        if (empty($categories)) {
            return '';
        }
        foreach ($categories as $key => $category) {
            $categories[$key] = $this->_getNodeJson($category);
        }
        return
            '<script type="text/javascript">'
            . $javascriptVarName . ' = ' . $this->_coreData->jsonEncode($categories) . ';'
            . ($this->canAddSubCategory()
                ? '$("add_subcategory_button").show();'
                : '$("add_subcategory_button").hide();')
            . '</script>';
    }

    /**
     * Get JSON of a tree node or an associative array
     *
     * @param Magento_Data_Tree_Node|array $node
     * @param int $level
     * @return string
     */
    protected function _getNodeJson($node, $level = 0)
    {
        // create a node from data array
        if (is_array($node)) {
            $node = new Magento_Data_Tree_Node($node, 'entity_id', new Magento_Data_Tree);
        }

        $item = array();
        $item['text'] = $this->buildNodeName($node);

        /* $rootForStores = Mage::getModel('Magento_Core_Model_Store')
            ->getCollection()
            ->loadByCategoryIds(array($node->getEntityId())); */
        $rootForStores = in_array($node->getEntityId(), $this->getRootIds());

        $item['id']  = $node->getId();
        $item['store']  = (int) $this->getStore()->getId();
        $item['path'] = $node->getData('path');

        $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
        //$item['allowDrop'] = ($level<3) ? true : false;
        $allowMove = $this->_isCategoryMoveable($node);
        $item['allowDrop'] = $allowMove;
        // disallow drag if it's first level and category is root of a store
        $item['allowDrag'] = $allowMove && (($node->getLevel()==1 && $rootForStores) ? false : true);

        if ((int)$node->getChildrenCount()>0) {
            $item['children'] = array();
        }

        $isParent = $this->_isParentSelectedCategory($node);

        if ($node->hasChildren()) {
            $item['children'] = array();
            if (!($this->getUseAjax() && $node->getLevel() > 1 && !$isParent)) {
                foreach ($node->getChildren() as $child) {
                    $item['children'][] = $this->_getNodeJson($child, $level+1);
                }
            }
        }

        if ($isParent || $node->getLevel() < 2) {
            $item['expanded'] = true;
        }

        return $item;
    }

    /**
     * Get category name
     *
     * @param Magento_Object $node
     * @return string
     */
    public function buildNodeName($node)
    {
        $result = $this->escapeHtml($node->getName());
        if ($this->_withProductCount) {
             $result .= ' (' . $node->getProductCount() . ')';
        }
        return $result;
    }

    protected function _isCategoryMoveable($node)
    {
        $options = new Magento_Object(array(
            'is_moveable' => true,
            'category' => $node
        ));

        $this->_eventManager->dispatch('adminhtml_catalog_category_tree_is_moveable',
            array('options'=>$options)
        );

        return $options->getIsMoveable();
    }

    protected function _isParentSelectedCategory($node)
    {
        if ($node && $this->getCategory()) {
            $pathIds = $this->getCategory()->getPathIds();
            if (in_array($node->getId(), $pathIds)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if page loaded by outside link to category edit
     *
     * @return boolean
     */
    public function isClearEdit()
    {
        return (bool) $this->getRequest()->getParam('clear');
    }

    /**
     * Check availability of adding root category
     *
     * @return boolean
     */
    public function canAddRootCategory()
    {
        $options = new Magento_Object(array('is_allow'=>true));
        $this->_eventManager->dispatch(
            'adminhtml_catalog_category_tree_can_add_root_category',
            array(
                'category' => $this->getCategory(),
                'options'   => $options,
                'store'    => $this->getStore()->getId()
            )
        );

        return $options->getIsAllow();
    }

    /**
     * Check availability of adding sub category
     *
     * @return boolean
     */
    public function canAddSubCategory()
    {
        $options = new Magento_Object(array('is_allow'=>true));
        $this->_eventManager->dispatch(
            'adminhtml_catalog_category_tree_can_add_sub_category',
            array(
                'category' => $this->getCategory(),
                'options'   => $options,
                'store'    => $this->getStore()->getId()
            )
        );

        return $options->getIsAllow();
    }
}
