<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Invitation
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Invitation view status history tab block
 *
 * @category   Magento
 * @package    Magento_Invitation
 */
namespace Magento\Invitation\Block\Adminhtml\Invitation\View\Tab;

class History
    extends \Magento\Adminhtml\Block\Template
    implements \Magento\Adminhtml\Block\Widget\Tab\TabInterface
{
    protected $_template = 'view/tab/history.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry;

    /**
     * Invitation History Factory
     *
     * @var \Magento\Invitation\Model\Invitation\HistoryFactory
     */
    protected $_historyFactory;

    /**
     * Application locale
     *
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Invitation\Model\Invitation\HistoryFactory $historyFactory
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Invitation\Model\Invitation\HistoryFactory $historyFactory,
        \Magento\Core\Model\LocaleInterface $locale,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($coreData, $context, $data);
        $this->_historyFactory = $historyFactory;
        $this->_locale = $locale;
    }

    public function getTabLabel()
    {
        return __('Status History');
    }

    public function getTabTitle()
    {
        return __('Status History');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    /**
     * Return Invitation for view
     *
     * @return \Magento\Invitation\Model\Invitation
     */
    public function getInvitation()
    {
        return $this->_coreRegistry->registry('current_invitation');
    }

    /**
     * Return invitation status history collection
     *
     * @return \Magento\Invitation\Model\Resource\Invitation\History\Collection
     */
    public function getHistoryCollection()
    {
        return $this->_historyFactory->create()
            ->getCollection()
            ->addFieldToFilter('invitation_id', $this->getInvitation()->getId())
            ->addOrder('history_id');
    }

    /**
     * Retrieve formating date
     *
     * @param   string $date
     * @param   string $format
     * @param   bool $showTime
     * @return  string
     */
    public function formatDate($date = null, $format = 'short', $showTime = false)
    {
        if (is_string($date)) {
            $date = $this->_locale->date($date, \Magento\Date::DATETIME_INTERNAL_FORMAT);
        }

        return parent::formatDate($date, $format, $showTime);
    }

    /**
     * Retrieve formatting time
     *
     * @param   string $date
     * @param   string $format
     * @param   bool $showDate
     * @return  string
     */
    public function formatTime($date = null, $format = 'short', $showDate = false)
    {
        if (is_string($date)) {
            $date = $this->_locale->date($date, \Magento\Date::DATETIME_INTERNAL_FORMAT);
        }

        return parent::formatTime($date, $format, $showDate);
    }
}
