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
 * Search Customer Model
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Model\Search;

class Customer extends \Magento\Object
{
    /**
     * Adminhtml data
     *
     * @var \Magento\Adminhtml\Helper\Data
     */
    protected $_adminhtmlData = null;

    /**
     * @var \Magento\Customer\Model\Resource\Customer\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Customer\Model\Resource\Customer\CollectionFactory $collectionFactory
     * @param \Magento\Adminhtml\Helper\Data $adminhtmlData
     */
    public function __construct(
        \Magento\Customer\Model\Resource\Customer\CollectionFactory $collectionFactory,
        \Magento\Adminhtml\Helper\Data $adminhtmlData
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_adminhtmlData = $adminhtmlData;
    }

    /**
     * Load search results
     *
     * @return \Magento\Adminhtml\Model\Search\Customer
     */
    public function load()
    {
        $result = array();
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($result);
            return $this;
        }

        $collection = $this->_collectionFactory->create()
            ->addNameToSelect()
            ->joinAttribute('company', 'customer_address/company', 'default_billing', null, 'left')
            ->addAttributeToFilter(array(
                array('attribute'=>'firstname', 'like' => $this->getQuery().'%'),
                array('attribute'=>'lastname', 'like'  => $this->getQuery().'%'),
                array('attribute'=>'company', 'like'   => $this->getQuery().'%'),
            ))
            ->setPage(1, 10)
            ->load();

        foreach ($collection->getItems() as $customer) {
            $result[] = array(
                'id'            => 'customer/1/'.$customer->getId(),
                'type'          => __('Customer'),
                'name'          => $customer->getName(),
                'description'   => $customer->getCompany(),
                'url' => $this->_adminhtmlData->getUrl('customer/customer/edit', array('id' => $customer->getId())),
            );
        }

        $this->setResults($result);

        return $this;
    }
}
