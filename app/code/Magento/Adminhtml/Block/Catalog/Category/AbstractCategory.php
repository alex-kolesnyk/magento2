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
 * Category abstract block
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Block\Catalog\Category;

class AbstractCategory extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Core\Helper\Data $coreData,
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Retrieve current category instance
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        return $this->_coreRegistry->registry('category');
    }

    public function getCategoryId()
    {
        if ($this->getCategory()) {
            return $this->getCategory()->getId();
        }
        return \Magento\Catalog\Model\Category::TREE_ROOT_ID;
    }

    public function getCategoryName()
    {
        return $this->getCategory()->getName();
    }

    public function getCategoryPath()
    {
        if ($this->getCategory()) {
            return $this->getCategory()->getPath();
        }
        return \Magento\Catalog\Model\Category::TREE_ROOT_ID;
    }

    public function hasStoreRootCategory()
    {
        $root = $this->getRoot();
        if ($root && $root->getId()) {
            return true;
        }
        return false;
    }

    public function getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        return \Mage::app()->getStore($storeId);
    }

    public function getRoot($parentNodeCategory=null, $recursionLevel=3)
    {
        if (!is_null($parentNodeCategory) && $parentNodeCategory->getId()) {
            return $this->getNode($parentNodeCategory, $recursionLevel);
        }
        $root = $this->_coreRegistry->registry('root');
        if (is_null($root)) {
            $storeId = (int) $this->getRequest()->getParam('store');

            if ($storeId) {
                $store = \Mage::app()->getStore($storeId);
                $rootId = $store->getRootCategoryId();
            }
            else {
                $rootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            }

            $tree = \Mage::getResourceSingleton('Magento\Catalog\Model\Resource\Category\Tree')
                ->load(null, $recursionLevel);

            if ($this->getCategory()) {
                $tree->loadEnsuredNodes($this->getCategory(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getCategoryCollection());

            $root = $tree->getNodeById($rootId);

            if ($root && $rootId != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $root->setIsVisible(true);
            }
            elseif($root && $root->getId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $root->setName(__('Root'));
            }

            $this->_coreRegistry->register('root', $root);
        }

        return $root;
    }

    /**
     * Get and register categories root by specified categories IDs
     *
     * IDs can be arbitrary set of any categories ids.
     * Tree with minimal required nodes (all parents and neighbours) will be built.
     * If ids are empty, default tree with depth = 2 will be returned.
     *
     * @param array $ids
     */
    public function getRootByIds($ids)
    {
        $root = $this->_coreRegistry->registry('root');
        if (null === $root) {
            $categoryTreeResource = \Mage::getResourceSingleton('Magento\Catalog\Model\Resource\Category\Tree');
            $ids    = $categoryTreeResource->getExistingCategoryIdsBySpecifiedIds($ids);
            $tree   = $categoryTreeResource->loadByIds($ids);
            $rootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            $root   = $tree->getNodeById($rootId);
            if ($root && $rootId != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $root->setIsVisible(true);
            } else if($root && $root->getId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $root->setName(__('Root'));
            }

            $tree->addCollectionData($this->getCategoryCollection());
            $this->_coreRegistry->register('root', $root);
        }
        return $root;
    }

    public function getNode($parentNodeCategory, $recursionLevel=2)
    {
        $tree = \Mage::getResourceModel('Magento\Catalog\Model\Resource\Category\Tree');

        $nodeId     = $parentNodeCategory->getId();
        $parentId   = $parentNodeCategory->getParentId();

        $node = $tree->loadNode($nodeId);
        $node->loadChildren($recursionLevel);

        if ($node && $nodeId != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
            $node->setIsVisible(true);
        } elseif($node && $node->getId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
            $node->setName(__('Root'));
        }

        $tree->addCollectionData($this->getCategoryCollection());

        return $node;
    }

    public function getSaveUrl(array $args = array())
    {
        $params = array('_current'=>true);
        $params = array_merge($params, $args);
        return $this->getUrl('*/*/save', $params);
    }

    public function getEditUrl()
    {
        return $this->getUrl("*/catalog_category/edit", array('_current'=>true, 'store'=>null, '_query'=>false, 'id'=>null, 'parent'=>null));
    }

    /**
     * Return ids of root categories as array
     *
     * @return array
     */
    public function getRootIds()
    {
        $ids = $this->getData('root_ids');
        if (is_null($ids)) {
            $ids = array();
            foreach (\Mage::app()->getGroups() as $store) {
                $ids[] = $store->getRootCategoryId();
            }
            $this->setData('root_ids', $ids);
        }
        return $ids;
    }
}
