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
 * Adminhtml newsletter subscribers grid website filter
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Block\Newsletter\Subscriber\Grid\Filter;

class Website extends \Magento\Adminhtml\Block\Widget\Grid\Column\Filter\Select
{

    protected $_websiteCollection = null;

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Core\Model\Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _getOptions()
    {
        $result = $this->getCollection()->toOptionArray();
        array_unshift($result, array('label'=>null, 'value'=>null));
        return $result;
    }

    public function getCollection()
    {
        if(is_null($this->_websiteCollection)) {
            $this->_websiteCollection = \Mage::getResourceModel('Magento\Core\Model\Resource\Website\Collection')
                ->load();
        }

        $this->_coreRegistry->register('website_collection', $this->_websiteCollection);

        return $this->_websiteCollection;
    }

    public function getCondition()
    {
        $id = $this->getValue();
        if (!$id) {
            return null;
        }

        $website = \Mage::app()->getWebsite($id);
        return array('in' => $website->getStoresIds(true));
    }
}
