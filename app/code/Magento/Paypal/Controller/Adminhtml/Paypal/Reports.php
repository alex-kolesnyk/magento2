<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Paypal
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * PayPal Settlement Reports Controller
 */
namespace Magento\Paypal\Controller\Adminhtml\Paypal;

class Reports extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Paypal\Model\Report\Settlement\RowFactory
     */
    protected $_rowFactory;

    /**
     * @var \Magento\Paypal\Model\Report\SettlementFactory
     */
    protected $_settlementFactory;

    /**
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\App\Action\Title
     */
    protected $_title;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\Paypal\Model\Report\Settlement\RowFactory $rowFactory
     * @param \Magento\Paypal\Model\Report\SettlementFactory $settlementFactory
     * @param \Magento\App\Action\Title $title
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\Paypal\Model\Report\Settlement\RowFactory $rowFactory,
        \Magento\Paypal\Model\Report\SettlementFactory $settlementFactory,
        \Magento\App\Action\Title $title
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_rowFactory = $rowFactory;
        $this->_settlementFactory = $settlementFactory;
        $this->_logger = $context->getLogger();
        parent::__construct($context);
        $this->_title = $title;
    }

    /**
     * Grid action
     */
    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }

    /**
     * Ajax callback for grid actions
     */
    public function gridAction()
    {
        $this->_layoutServices->loadLayout(false);
        $this->_layoutServices->renderLayout();
    }

    /**
     * View transaction details action
     */
    public function detailsAction()
    {
        $rowId = $this->getRequest()->getParam('id');
        $row = $this->_rowFactory->create()->load($rowId);
        if (!$row->getId()) {
            $this->_redirect('adminhtml/*/');
            return;
        }
        $this->_coreRegistry->register('current_transaction', $row);
        $this->_initAction()
            ->_title->add(__('View Transaction'))
            ->_addContent($this->_layoutServices->getLayout()
                ->createBlock('Magento\Paypal\Block\Adminhtml\Settlement\Details', 'settlementDetails'))
            ->renderLayout();
    }

    /**
     * Forced fetch reports action
     *
     * @throws \Magento\Core\Exception
     */
    public function fetchAction()
    {
        try {
            $reports = $this->_settlementFactory->create();
            /* @var $reports \Magento\Paypal\Model\Report\Settlement */
            $credentials = $reports->getSftpCredentials();
            if (empty($credentials)) {
                throw new \Magento\Core\Exception(__('We found nothing to fetch because of an empty configuration.'));
            }
            foreach ($credentials as $config) {
                try {
                    $fetched = $reports->fetchAndSave(
                        \Magento\Paypal\Model\Report\Settlement::createConnection($config)
                    );
                    $this->_getSession()->addSuccess(
                        __("We fetched %1 report rows from '%2@%3'.", $fetched,
                            $config['username'], $config['hostname'])
                    );
                } catch (\Exception $e) {
                    $this->_getSession()->addError(
                        __("We couldn't fetch reports from '%1@%2'.", $config['username'], $config['hostname'])
                    );
                    $this->_logger->logException($e);
                }
            }
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_logger->logException($e);
        }
        $this->_redirect('adminhtml/*/index');
    }

    /**
     * Initialize titles, navigation
     * @return \Magento\Paypal\Controller\Adminhtml\Paypal\Reports
     */
    protected function _initAction()
    {
        $this->_title->add(__('PayPal Settlement Reports'));
        $this->_layoutServices->loadLayout();
        $this->_setActiveMenu('Magento_Paypal::report_salesroot_paypal_settlement_reports')
            ->_addBreadcrumb(__('Reports'), __('Reports'))
            ->_addBreadcrumb(__('Sales'), __('Sales'))
            ->_addBreadcrumb(__('PayPal Settlement Reports'), __('PayPal Settlement Reports'));
        return $this;
    }

    /**
     * ACL check
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'index':
            case 'details':
                return $this->_authorization->isAllowed('Magento_Paypal::paypal_settlement_reports_view');
            case 'fetch':
                return $this->_authorization->isAllowed('Magento_Paypal::fetch');
            default:
                return $this->_authorization->isAllowed('Magento_Paypal::paypal_settlement_reports');
        }
    }
}
