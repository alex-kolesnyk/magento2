<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_VersionsCms
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Cms page edit form revisions tab
 *
 * @category    Magento
 * @package     Magento_VersionsCms
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\VersionsCms\Block\Adminhtml\Cms\Page\Edit\Tab;

class Versions
    extends \Magento\Backend\Block\Widget\Grid\Extended
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Array of admin users in system
     * @var array
     */
    protected $_usersHash = null;

    /**
     * Cms data
     *
     * @var \Magento\VersionsCms\Helper\Data
     */
    protected $_cmsData = null;

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\VersionsCms\Helper\Data $cmsData
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Url $urlModel
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\VersionsCms\Helper\Data $cmsData,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Url $urlModel,
        \Magento\Core\Model\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_cmsData = $cmsData;
        parent::__construct($coreData, $context, $storeManager, $urlModel, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->setUseAjax(true);
        $this->setId('versions');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepares collection of versions
     *
     * @return \Magento\VersionsCms\Block\Adminhtml\Cms\Page\Edit\Tab\Versions
     */
    protected function _prepareCollection()
    {
        $userId = \Mage::getSingleton('Magento\Backend\Model\Auth\Session')->getUser()->getId();

        /* var $collection Magento_VersionsCms_Model_Resource_Version_Collection */
        $collection = \Mage::getModel('Magento\VersionsCms\Model\Page\Version')->getCollection()
            ->addPageFilter($this->getPage())
            ->addVisibilityFilter($userId,
                \Mage::getSingleton('Magento\VersionsCms\Model\Config')->getAllowedAccessLevel())
            ->addUserColumn()
            ->addUserNameColumn();

        if (!$this->getParam($this->getVarNameSort())) {
            $collection->addNumberSort();
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Retrieve collection for grid if there is not collection call _prepareCollection
     *
     * @return \Magento\VersionsCms\Model\Resource\Page\Version\Collection
     */
    public function getCollection()
    {
        if (!$this->_collection) {
            $this->_prepareCollection();
        }

        return $this->_collection;
    }

    /**
     * Prepare versions grid columns
     *
     * @return \Magento\VersionsCms\Block\Adminhtml\Cms\Page\Edit\Tab\Versions
     */
    protected function _prepareColumns()
    {
        $this->addColumn('label', array(
            'header' => __('Version Label'),
            'index' => 'label',
            'type' => 'options',
            'options' => $this->getCollection()
                                ->getAsArray('label', 'label')
        ));

        $this->addColumn('owner', array(
            'header' => __('Owner'),
            'index' => 'username',
            'type' => 'options',
            'options' => $this->getCollection()->getUsersArray(false),
            'width' => 250
        ));

        $this->addColumn('access_level', array(
            'header' => __('Access Level'),
            'index' => 'access_level',
            'type' => 'options',
            'width' => 100,
            'options' => $this->_cmsData->getVersionAccessLevels()
        ));

        $this->addColumn('revisions', array(
            'header' => __('Quantity'),
            'index' => 'revisions_count',
            'type' => 'number'
        ));

        $this->addColumn('created_at', array(
            'width'     => 150,
            'header'    => __('Created'),
            'index'     => 'created_at',
            'type'      => 'datetime',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Prepare url for reload grid through ajax
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/versions', array('_current'=>true));
    }

    /**
     * Returns cms page object from registry
     *
     * @return \Magento\Cms\Model\Page
     */
    public function getPage()
    {
        return $this->_coreRegistry->registry('cms_page');
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Versions');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Versions');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare massactions for this grid.
     * For now it is only ability to remove versions
     *
     * @return \Magento\VersionsCms\Block\Adminhtml\Cms\Page\Edit\Tab\Versions
     */
    protected function _prepareMassaction()
    {
        if (\Mage::getSingleton('Magento\VersionsCms\Model\Config')->canCurrentUserDeleteVersion()) {
            $this->setMassactionIdField('version_id');
            $this->getMassactionBlock()->setFormFieldName('version');

            $this->getMassactionBlock()->addItem('delete', array(
                'label'    => __('Delete'),
                'url'      => $this->getUrl('*/*/massDeleteVersions', array('_current' => true)),
                'confirm'  => __('Are you sure?'),
                'selected' => true,
            ));
        }
        return $this;
    }

    /**
     * Grid row event edit url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/cms_page_version/edit', array('page_id' => $row->getPageId(), 'version_id' => $row->getVersionId()));
    }
}
