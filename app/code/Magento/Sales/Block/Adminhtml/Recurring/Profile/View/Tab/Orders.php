<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Recurring profile orders grid
 */
namespace Magento\Sales\Block\Adminhtml\Recurring\Profile\View\Tab;

class Orders
    extends \Magento\Adminhtml\Block\Widget\Grid
    implements \Magento\Adminhtml\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Url $urlModel
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Url $urlModel,
        \Magento\Core\Model\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($coreData, $context, $storeManager, $urlModel, $data);
    }

    /**
     * Initialize basic parameters
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('recurring_profile_orders')
            ->setUseAjax(true)
            ->setSkipGenerateContent(true);
    }

    /**
     * Prepare grid collection object
     *
     * @return \Magento\Sales\Block\Adminhtml\Recurring\Profile\View\Tab\Orders
     */
    protected function _prepareCollection()
    {
        $collection = \Mage::getResourceModel('Magento\Sales\Model\Resource\Order\Grid\Collection')
            ->addRecurringProfilesFilter($this->_coreRegistry->registry('current_recurring_profile')->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * TODO: fix up this mess
     *
     * @return \Magento\Sales\Block\Adminhtml\Recurring\Profile\View\Tab\Orders
     */
    protected function _prepareColumns()
    {
        $this->addColumn('real_order_id', array(
            'header'=> __('Order'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
        ));

        if (!\Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => __('Purchase Point'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => __('Purchased Date'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('billing_name', array(
            'header' => __('Bill-to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header' => __('Ship-to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => __('Grand Total (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => __('Grand Total (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => __('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => \Mage::getSingleton('Magento\Sales\Model\Order\Config')->getStatuses(),
        ));

        if ($this->_authorization->isAllowed('Magento_Sales::actions_view')) {
            $this->addColumn('action',
                array(
                    'header'    => __('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => __('View'),
                            'url'     => array('base'=>'*/sales_order/view'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
        }

        return parent::_prepareColumns();
    }

    /**
     * Return row url for js event handlers
     *
     * @param \Magento\Object
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
    }

    /**
     * Url for ajax grid submission
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getTabUrl();
    }

    /**
     * Url for ajax tab
     *
     * @return string
     */
    public function getTabUrl()
    {
        $recurringProfile = $this->_coreRegistry->registry('current_recurring_profile');
        return $this->getUrl('*/*/orders', array('profile' => $recurringProfile->getId()));
    }

    /**
     * Class for ajax tab
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax';
    }

    /**
     * Label getter
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Related Orders');
    }

    /**
     * Same as label getter
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
