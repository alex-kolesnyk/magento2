<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Reward
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Customer account reward history block
 *
 * @category    Magento
 * @package     Magento_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reward\Block\Customer\Reward;

class History extends \Magento\Core\Block\Template
{
    /**
     * History records collection
     *
     * @var \Magento\Reward\Model\Resource\Reward\History\Collection
     */
    protected $_collection = null;

    /**
     * Reward data
     *
     * @var \Magento\Reward\Helper\Data
     */
    protected $_rewardData = null;

    /**
     * @param \Magento\Reward\Helper\Data $rewardData
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Reward\Helper\Data $rewardData,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->_rewardData = $rewardData;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Get history collection if needed
     *
     * @return \Magento\Reward\Model\Resource\Reward\History\Collection|false
     */
    public function getHistory()
    {
        if (0 == $this->_getCollection()->getSize()) {
            return false;
        }
        return $this->_collection;
    }

    /**
     * History item points delta getter
     *
     * @param \Magento\Reward\Model\Reward\History $item
     * @return string
     */
    public function getPointsDelta(\Magento\Reward\Model\Reward\History $item)
    {
        return $this->_rewardData->formatPointsDelta($item->getPointsDelta());
    }

    /**
     * History item points balance getter
     *
     * @param \Magento\Reward\Model\Reward\History $item
     * @return string
     */
    public function getPointsBalance(\Magento\Reward\Model\Reward\History $item)
    {
        return $item->getPointsBalance();
    }

    /**
     * History item currency balance getter
     *
     * @param \Magento\Reward\Model\Reward\History $item
     * @return string
     */
    public function getCurrencyBalance(\Magento\Reward\Model\Reward\History $item)
    {
        return $this->_coreData->currency($item->getCurrencyAmount());
    }

    /**
     * History item reference message getter
     *
     * @param \Magento\Reward\Model\Reward\History $item
     * @return string
     */
    public function getMessage(\Magento\Reward\Model\Reward\History $item)
    {
        return $item->getMessage();
    }

    /**
     * History item reference additional explanation getter
     *
     * @param \Magento\Reward\Model\Reward\History $item
     * @return string
     */
    public function getExplanation(\Magento\Reward\Model\Reward\History $item)
    {
        return ''; // TODO
    }

    /**
     * History item creation date getter
     *
     * @param \Magento\Reward\Model\Reward\History $item
     * @return string
     */
    public function getDate(\Magento\Reward\Model\Reward\History $item)
    {
        return $this->_coreData->formatDate($item->getCreatedAt(), 'short', true);
    }

    /**
     * History item expiration date getter
     *
     * @param \Magento\Reward\Model\Reward\History $item
     * @return string
     */
    public function getExpirationDate(\Magento\Reward\Model\Reward\History $item)
    {
        $expiresAt = $item->getExpiresAt();
        if ($expiresAt) {
            return $this->_coreData->formatDate($expiresAt, 'short', true);
        }
        return '';
    }

    /**
     * Return reword points update history collection by customer and website
     *
     * @return \Magento\Reward\Model\Resource\Reward\History\Collection
     */
    protected function _getCollection()
    {
        if (!$this->_collection) {
            $websiteId = \Mage::app()->getWebsite()->getId();
            $this->_collection = \Mage::getModel('Magento\Reward\Model\Reward\History')->getCollection()
                ->addCustomerFilter(\Mage::getSingleton('Magento\Customer\Model\Session')->getCustomerId())
                ->addWebsiteFilter($websiteId)
                ->setExpiryConfig($this->_rewardData->getExpiryConfig())
                ->addExpirationDate($websiteId)
                ->skipExpiredDuplicates()
                ->setDefaultOrder()
            ;
        }
        return $this->_collection;
    }

    /**
     * Instantiate Pagination
     *
     * @return \Magento\Reward\Block\Customer\Reward\History
     */
    protected function _prepareLayout()
    {
        if ($this->_isEnabled()) {
            $pager = $this->getLayout()->createBlock('Magento\Page\Block\Html\Pager', 'reward.history.pager')
                ->setCollection($this->_getCollection())->setIsOutputRequired(false)
            ;
            $this->setChild('pager', $pager);
        }
        return parent::_prepareLayout();
    }

    /**
     * Whether the history may show up
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_isEnabled()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Whether the history is supposed to be rendered
     *
     * @return bool
     */
    protected function _isEnabled()
    {
        return $this->_rewardData->isEnabledOnFront()
            && $this->_rewardData->getGeneralConfig('publish_history');
    }
}
