<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Rss
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Review form block
 */
namespace Magento\Rss\Block\Catalog;

class Salesrule extends \Magento\Rss\Block\AbstractBlock
{
    /**
     * @var \Magento\Rss\Model\RssFactory
     */
    protected $_rssFactory;

    /**
     * @var \Magento\SalesRule\Model\Resource\Rule\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Rss\Model\RssFactory $rssFactory
     * @param \Magento\SalesRule\Model\Resource\Rule\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Block\Template\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Rss\Model\RssFactory $rssFactory,
        \Magento\SalesRule\Model\Resource\Rule\CollectionFactory $collectionFactory,
        array $data = array()
    ) {
        $this->_rssFactory = $rssFactory;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $coreData, $storeManager, $customerSession, $data);
    }

    protected function _construct()
    {
        /*
        * setting cache to save the rss for 10 minutes
        */
        $this->setCacheKey('rss_catalog_salesrule_' . $this->getStoreId() . '_' . $this->_getCustomerGroupId());
        $this->setCacheLifetime(600);
    }

    /**
     * Generate RSS XML with sales rules data
     *
     * @return string
     */
    protected function _toHtml()
    {
        $storeId       = $this->_getStoreId();
        $storeModel    = $this->_storeManager->getStore($storeId);
        $websiteId     = $storeModel->getWebsiteId();
        $customerGroup = $this->_getCustomerGroupId();
        $now           = date('Y-m-d');
        $url           = $this->_urlBuilder->getUrl('');
        $newUrl        = $this->_urlBuilder->getUrl('rss/catalog/salesrule');
        $lang          = $storeModel->getConfig('general/locale/code');
        $title         = __('%1 - Discounts and Coupons', $storeModel->getName());

        /** @var $rssObject \Magento\Rss\Model\Rss */
        $rssObject = $this->_rssFactory->create();
        $rssObject->_addHeader(array(
            'title'       => $title,
            'description' => $title,
            'link'        => $newUrl,
            'charset'     => 'UTF-8',
            'language'    => $lang
        ));

        /** @var $collection \Magento\SalesRule\Model\Resource\Rule\Collection */
        $collection = $this->_collectionFactory->create();
        $collection->addWebsiteGroupDateFilter($websiteId, $customerGroup, $now)
            ->addFieldToFilter('is_rss', 1)
            ->setOrder('from_date','desc');
        $collection->load();

        /** @var $ruleModel \Magento\SalesRule\Model\Rule */
        foreach ($collection as $ruleModel) {
            $description = '<table><tr>'
                . '<td style="text-decoration:none;">'.$ruleModel->getDescription()
                . '<br/>Discount Start Date: '.$this->formatDate($ruleModel->getFromDate(), 'medium');
            if ($ruleModel->getToDate()) {
                $description .= '<br/>Discount End Date: ' . $this->formatDate($ruleModel->getToDate(), 'medium');
            }
            if ($ruleModel->getCouponCode()) {
                $description .= '<br/> Coupon Code: '. $ruleModel->getCouponCode();
            }
            $description .=  '</td></tr></table>';
            $rssObject->_addEntry(array(
                'title'       => $ruleModel->getName(),
                'description' => $description,
                'link'        => $url
            ));
        }

        return $rssObject->createRssXml();
    }
}
