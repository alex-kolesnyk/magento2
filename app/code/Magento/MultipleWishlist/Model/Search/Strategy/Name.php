<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_MultipleWishlist
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Wishlist search by name and last name strategy
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
namespace Magento\MultipleWishlist\Model\Search\Strategy;

class Name implements \Magento\MultipleWishlist\Model\Search\Strategy\StrategyInterface
{
    /**
     * Customer firstname provided for search
     *
     * @var string
     */
    protected $_firstname;

    /**
     * Customer lastname provided for search
     *
     * @var string
     */
    protected $_lastname;

    /**
     * Customer collection factory
     *
     * @var \Magento\Customer\Model\Resource\Customer\CollectionFactory
     */
    protected $_customerCollectionFactory;

    /**
     * Construct
     *
     * @param \Magento\Customer\Model\Resource\Customer\CollectionFactory $customerCollectionFactory
     */
    public function __construct(
        \Magento\Customer\Model\Resource\Customer\CollectionFactory $customerCollectionFactory
    ) {
        $this->_customerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * Validate search params
     *
     * @param array $params
     * @throws \InvalidArgumentException
     */
    public function setSearchParams(array $params)
    {
        if (empty($params['firstname']) || strlen($params['firstname']) < 2) {
            throw new \InvalidArgumentException(__('Please enter at least 2 letters of the first name.'));
        }
        $this->_firstname = $params['firstname'];
        if (empty($params['lastname']) || strlen($params['lastname']) < 2) {
            throw new \InvalidArgumentException(__('Please enter at least 2 letters of the last name.'));
        }
        $this->_lastname = $params['lastname'];
    }

    /**
     * Filter wishlist collection
     *
     * @param \Magento\Wishlist\Model\Resource\Wishlist\Collection $collection
     * @return \Magento\Wishlist\Model\Resource\Wishlist\Collection
     */
    public function filterCollection(\Magento\Wishlist\Model\Resource\Wishlist\Collection $collection)
    {
        /* @var $customers \Magento\Customer\Model\Resource\Customer\Collection */
        $customers = $this->_customerCollectionFactory->create();
        $customers->addAttributeToFilter(
                array(array('attribute' => 'firstname', 'like' => '%'.$this->_firstname.'%'))
            )
            ->addAttributeToFilter(
                array(array('attribute' => 'lastname', 'like' => '%'.$this->_lastname.'%'))
            );

        $collection->filterByCustomerIds($customers->getAllIds());
        foreach ($collection as $wishlist) {
            $wishlist->setCustomer($customers->getItemById($wishlist->getCustomerId()));
        }
        return $collection;
    }
}
