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
 * Billing agreements controller
 */
namespace Magento\Sales\Controller\Billing;

use Magento\App\Action\NotFoundException;
use Magento\App\RequestInterface;

class Agreement extends \Magento\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\App\Action\Title
     */
    protected $_title;

    /**
     * @param \Magento\App\Action\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\App\Action\Title $title
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\App\Action\Title $title
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->_title = $title;
    }

    /**
     * View billing agreements
     *
     */
    public function indexAction()
    {
        $this->_title->add(__('Billing Agreements'));
        $this->_layoutServices->loadLayout();
        $this->_layoutServices->getLayout()->initMessages('Magento\Customer\Model\Session');
        $this->_layoutServices->renderLayout();
    }


    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return mixed
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$request->isDispatched()) {
            return parent::dispatch($request);
        }
        if (!$this->_getSession()->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    /**
     * View billing agreement
     *
     */
    public function viewAction()
    {
        if (!$agreement = $this->_initAgreement()) {
            return;
        }
        $this->_title->add(__('Billing Agreements'))
            ->_title->add(__('Billing Agreement # %1', $agreement->getReferenceId()));
        $this->_layoutServices->loadLayout();
        $this->_layoutServices->getLayout()->initMessages('Magento\Customer\Model\Session');
        $navigationBlock = $this->_layoutServices->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/billing_agreement/');
        }
        $this->_layoutServices->renderLayout();
    }

    /**
     * Wizard start action
     *
     */
    public function startWizardAction()
    {
        $agreement = $this->_objectManager->create('Magento\Sales\Model\Billing\Agreement');
        $paymentCode = $this->getRequest()->getParam('payment_method');
        if ($paymentCode) {
            try {
                $agreement
                    ->setStoreId($this->_objectManager->get('Magento\Core\Model\StoreManager')->getStore()->getId())
                    ->setMethodCode($paymentCode)
                    ->setReturnUrl($this->_objectManager->create('Magento\Core\Model\Url')
                        ->getUrl('*/*/returnWizard', array('payment_method' => $paymentCode)))
                    ->setCancelUrl($this->_objectManager->create('Magento\Core\Model\Url')
                        ->getUrl('*/*/cancelWizard', array('payment_method' => $paymentCode)));

                $this->getResponse()->setRedirect($agreement->initToken());
                return $this;
            } catch (\Magento\Core\Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Logger')->logException($e);
                $this->_getSession()->addError(__('We couldn\'t start the billing agreement wizard.'));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Wizard return action
     *
     */
    public function returnWizardAction()
    {
        $agreement = $this->_objectManager->create('Magento\Sales\Model\Billing\Agreement');
        $paymentCode = $this->getRequest()->getParam('payment_method');
        $token = $this->getRequest()->getParam('token');
        if ($token && $paymentCode) {
            try {
                $agreement
                    ->setStoreId($this->_objectManager->get('Magento\Core\Model\StoreManager')->getStore()->getId())
                    ->setToken($token)
                    ->setMethodCode($paymentCode)
                    ->setCustomer($this->_objectManager->get('Magento\Customer\Model\Session')->getCustomer())
                    ->place();
                $this->_getSession()->addSuccess(
                    __('The billing agreement "%1" has been created.', $agreement->getReferenceId())
                );
                $this->_redirect('*/*/view', array('agreement' => $agreement->getId()));
                return;
            } catch (\Magento\Core\Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Logger')->logException($e);
                $this->_getSession()->addError(__('We couldn\'t finish the billing agreement wizard.'));
            }
            $this->_redirect('*/*/index');
        }
    }

    /**
     * Wizard cancel action
     *
     */
    public function cancelWizardAction()
    {
        $this->_redirect('*/*/index');
    }

    /**
     * Cancel action
     * Set billing agreement status to 'Canceled'
     *
     */
    public function cancelAction()
    {
        $agreement = $this->_initAgreement();
        if ($agreement && $agreement->canCancel()) {
            try {
                $agreement->cancel();
                $this->_getSession()->addNotice(
                    __('The billing agreement "%1" has been canceled.', $agreement->getReferenceId())
                );
            } catch (\Magento\Core\Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Logger')->logException($e);
                $this->_getSession()->addError(__('We couldn\'t cancel the billing agreement.'));
            }
        }
        $this->_redirect('*/*/view', array('_current' => true));
    }

    /**
     * Init billing agreement model from request
     *
     * @return \Magento\Sales\Model\Billing\Agreement
     */
    protected function _initAgreement()
    {
        $agreementId = $this->getRequest()->getParam('agreement');
        if ($agreementId) {
            $billingAgreement = $this->_objectManager->create('Magento\Sales\Model\Billing\Agreement')
                ->load($agreementId);
            if (!$billingAgreement->getAgreementId()) {
                $this->_getSession()->addError(__('Please specify the correct billing agreement ID and try again.'));
                $this->_redirect('*/*/');
                return false;
            }
        }
        $this->_coreRegistry->register('current_billing_agreement', $billingAgreement);
        return $billingAgreement;
    }

    /**
     * Retrieve customer session model
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_objectManager->get('Magento\Customer\Model\Session');
    }
}
