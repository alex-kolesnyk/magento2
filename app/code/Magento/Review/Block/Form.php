<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Review
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Review form block
 *
 * @category   Magento
 * @package    Magento_Review
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Review\Block;

class Form extends \Magento\Core\Block\Template
{
    /**
     * Review data
     *
     * @var \Magento\Review\Helper\Data
     */
    protected $_reviewData = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Rating\Model\RatingFactory
     */
    protected $_ratingFactory;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Review\Model\Session
     */
    protected $_reviewSession;

    /**
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Model\Session\Generic $reviewSession
     * @param \Magento\Review\Helper\Data $reviewData
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Rating\Model\RatingFactory $ratingFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Block\Template\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Model\Session\Generic $reviewSession,
        \Magento\Review\Helper\Data $reviewData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Rating\Model\RatingFactory $ratingFactory,
        array $data = array()
    ) {
        $this->_reviewSession = $reviewSession;
        $this->_reviewData = $reviewData;
        $this->_customerSession = $customerSession;
        $this->_productFactory = $productFactory;
        $this->_ratingFactory = $ratingFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $coreData, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $data = $this->_reviewSession->getFormData(true);
        $data = new \Magento\Object((array)$data);

        // add logged in customer name as nickname
        if (!$data->getNickname()) {
            $customer = $this->_customerSession->getCustomer();
            if ($customer && $customer->getId()) {
                $data->setNickname($customer->getFirstname());
            }
        }

        $this->setAllowWriteReviewFlag(
            $this->_customerSession->isLoggedIn() || $this->_reviewData->getIsGuestAllowToWrite()
        );
        if (!$this->getAllowWriteReviewFlag()) {
            $queryParam = $this->_coreData->urlEncode(
                $this->getUrl('*/*/*', array('_current' => true)) . '#review-form'
            );
            $this->setLoginLink($this->getUrl(
                    'customer/account/login/',
                    array(\Magento\Customer\Helper\Data::REFERER_QUERY_PARAM_NAME => $queryParam)
                )
            );
        }

        $this->setTemplate('form.phtml')
            ->assign('data', $data)
            ->assign('messages', $this->_reviewSession->getMessages(true));
    }

    public function getProductInfo()
    {
        $product = $this->_productFactory->create();
        return $product->load($this->getRequest()->getParam('id'));
    }

    public function getAction()
    {
        $productId = $this->getRequest()->getParam('id', false);
        return $this->getUrl('review/product/post', array('id' => $productId));
    }

    public function getRatings()
    {
        return $this->_ratingFactory->create()
            ->getResourceCollection()
            ->addEntityFilter('product')
            ->setPositionOrder()
            ->addRatingPerStoreName($this->_storeManager->getStore()->getId())
            ->setStoreFilter($this->_storeManager->getStore()->getId())
            ->setActiveFilter(true)
            ->load()
            ->addOptionToItems();
    }
}
