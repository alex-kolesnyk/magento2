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
 * Report statistics admin controller
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Controller\Report;

class Statistics extends \Magento\Backend\Controller\Adminhtml\Action
{
    /**
     * Admin session model
     *
     * @var null|\Magento\Backend\Model\Auth\Session
     */
    protected $_adminSession = null;

    public function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(__('Reports'), __('Reports'))
            ->_addBreadcrumb(__('Statistics'), __('Statistics'));
        return $this;
    }

    public function _initReportAction($blocks)
    {
        if (!is_array($blocks)) {
            $blocks = array($blocks);
        }

        $requestData = $this->_objectManager->get('Magento\Adminhtml\Helper\Data')
            ->prepareFilterString($this->getRequest()->getParam('filter'));
        $requestData = $this->_filterDates($requestData, array('from', 'to'));
        $requestData['store_ids'] = $this->getRequest()->getParam('store_ids');
        $params = new \Magento\Object();

        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $params->setData($key, $value);
            }
        }

        foreach ($blocks as $block) {
            if ($block) {
                $block->setPeriodType($params->getData('period_type'));
                $block->setFilterData($params);
            }
        }

        return $this;
    }

    /**
     * Retrieve array of collection names by code specified in request
     *
     * @return array
     */
    protected function _getCollectionNames()
    {
        $codes = $this->getRequest()->getParam('code');
        if (!$codes) {
            throw new \Exception(__('No report code is specified.'));
        }

        if(!is_array($codes) && strpos($codes, ',') === false) {
            $codes = array($codes);
        } elseif (!is_array($codes)) {
            $codes = explode(',', $codes);
        }

        $aliases = array(
            'sales'       => 'Magento\Sales\Model\Resource\Report\Order',
            'tax'         => 'Magento\Tax\Model\Resource\Report\Tax',
            'shipping'    => 'Magento\Sales\Model\Resource\Report\Shipping',
            'invoiced'    => 'Magento\Sales\Model\Resource\Report\Invoiced',
            'refunded'    => 'Magento\Sales\Model\Resource\Report\Refunded',
            'coupons'     => 'Magento\SalesRule\Model\Resource\Report\Rule',
            'bestsellers' => 'Magento\Sales\Model\Resource\Report\Bestsellers',
            'viewed'      => 'Magento\Reports\Model\Resource\Report\Product\Viewed',
        );
        $out = array();
        foreach ($codes as $code) {
            $out[] = $aliases[$code];
        }
        return $out;
    }

    /**
     * Refresh statistics for last 25 hours
     *
     * @return \Magento\Adminhtml\Controller\Report\Sales
     */
    public function refreshRecentAction()
    {
        try {
            $collectionsNames = $this->_getCollectionNames();
            $currentDate = $this->_objectManager->get('Magento\Core\Model\LocaleInterface')->date();
            $date = $currentDate->subHour(25);
            foreach ($collectionsNames as $collectionName) {
                $this->_objectManager->create($collectionName)->aggregate($date);
            }
            $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                ->addSuccess(__('Recent statistics have been updated.'));
        } catch (\Magento\Core\Exception $e) {
            $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                ->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                ->addError(__('We can\'t refresh recent statistics.'));
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
        }

        if($this->_getSession()->isFirstPageAfterLogin()) {
            $this->_redirect('adminhtml/*');
        } else {
            $this->_redirectReferer('*/*');
        }
        return $this;
    }

    /**
     * Refresh statistics for all period
     *
     * @return \Magento\Adminhtml\Controller\Report\Sales
     */
    public function refreshLifetimeAction()
    {
        try {
            $collectionsNames = $this->_getCollectionNames();
            foreach ($collectionsNames as $collectionName) {
                $this->_objectManager->create($collectionName)->aggregate();
            }
            $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                ->addSuccess(__('We updated lifetime statistics.'));
        } catch (\Magento\Core\Exception $e) {
            $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                ->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                ->addError(__('We can\'t refresh lifetime statistics.'));
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
        }

        if($this->_getSession()->isFirstPageAfterLogin()) {
            $this->_redirect('adminhtml/*');
        } else {
            $this->_redirectReferer('*/*');
        }

        return $this;
    }

    public function indexAction()
    {
        $this->_title(__('Refresh Statistics'));

        $this->_initAction()
            ->_setActiveMenu('Magento_Reports::report_statistics_refresh')
            ->_addBreadcrumb(__('Refresh Statistics'), __('Refresh Statistics'))
            ->renderLayout();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Reports::statistics');
    }

    /**
     * Retrieve admin session model
     *
     * @return \Magento\Backend\Model\Auth\Session
     */
    protected function _getSession()
    {
        if (is_null($this->_adminSession)) {
            $this->_adminSession = $this->_objectManager->get('Magento\Backend\Model\Auth\Session');
        }
        return $this->_adminSession;
    }
}
