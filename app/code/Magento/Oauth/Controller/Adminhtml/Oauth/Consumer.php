<?php
/**
 * {license_notice}
 *
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Manage consumers controller
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Magento_Oauth_Controller_Adminhtml_Oauth_Consumer extends Magento_Backend_Controller_ActionAbstract
{
    /** Param Key for extracting consumer id from Request */
    const PARAM_CONSUMER_ID = 'id';

    /** Data keys for extracting information from Consumer data array */
    const DATA_ENTITY_ID = 'entity_id';
    const DATA_KEY = 'key';
    const DATA_SECRET = 'secret';

    /** Keys used for registering data into the registry */
    const REGISTRY_KEY_CURRENT_CONSUMER = 'current_consumer';

    /** Key use for storing/retrieving consumer data in/from the session */
    const SESSION_KEY_CONSUMER_DATA = 'consumer_data';

    /** @var Magento_Core_Model_Registry  */
    private $_registry;

    /** @var Magento_Oauth_Model_Consumer_Factory */
    private $_consumerFactory;

    /** @var Magento_Oauth_Service_OauthInterfaceV1 */
    private $_oauthService;

    /** @var Magento_Oauth_Helper_Data */
    protected $_oauthHelper;

    /**
     * Class constructor
     *
     * @param Magento_Core_Model_Registry $registry
     * @param Magento_Core_Model_Factory_Helper $helperFactory
     * @param Magento_Oauth_Model_Consumer_Factory $consumerFactory
     * @param Magento_Oauth_Service_OauthInterfaceV1 $oauthService
     * @param Magento_Backend_Controller_Context $context
     * @param string $areaCode
     */
    public function __construct(
        Magento_Core_Model_Registry $registry,
        Magento_Core_Model_Factory_Helper $helperFactory,
        Magento_Oauth_Model_Consumer_Factory $consumerFactory,
        Magento_Oauth_Service_OauthInterfaceV1 $oauthService,
        Magento_Backend_Controller_Context $context,
        $areaCode = null
    ) {
        parent::__construct($context, $areaCode);
        $this->_registry = $registry;
        $this->_oauthHelper = $helperFactory->get('Magento_Oauth_Helper_Data');
        $this->_consumerFactory = $consumerFactory;
        $this->_oauthService = $oauthService;
    }

    /**
     * Perform layout initialization actions
     *
     * @return Magento_Oauth_Adminhtml_Oauth_ConsumerController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Magento_Oauth::system_api_oauth_consumer');
        return $this;
    }

    /**
     * Unset unused data from request
     * Skip getting "key" and "secret" because its generated from server side only
     *
     * @param array $data
     * @return array
     */
    protected function _filter(array $data)
    {
        foreach (array(self::PARAM_CONSUMER_ID, self::DATA_KEY, self::DATA_SECRET, 'back', 'form_key') as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }
        return $data;
    }

    /**
     * Retrieve the consumer.
     *
     * @param int $consumerId - The ID of the consumer
     * @return Magento_Oauth_Model_Consumer
     */
    protected function _fetchConsumer($consumerId)
    {
        $consumer = $this->_consumerFactory->create();

        if (!$consumerId) {
            $this->_getSession()->addError(__('Invalid ID parameter.'));
            $this->_redirect('*/*/index');
            return $consumer;
        }

        $consumer = $consumer->load($consumerId);

        if (!$consumer->getId()) {
            $this->_getSession()
                ->addError(__('An add-on with ID %s was not found.', $consumerId));
            $this->_redirect('*/*/index');
        }

        return $consumer;
    }

    /**
     * Init titles
     *
     * @return Magento_Oauth_Adminhtml_Oauth_ConsumerController
     */
    public function preDispatch()
    {
        $this->_title(__('Add-Ons'));
        parent::preDispatch();
        return $this;
    }

    /**
     * Render grid page
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Render grid AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Create new consumer action
     */
    public function newAction()
    {
        $consumer = $this->_consumerFactory->create();

        $formData = $this->_getFormData();
        if ($formData) {
            $this->_setFormData($formData);
            $consumer->addData($formData);
        } else {
            $consumer->setData(self::DATA_KEY, $this->_oauthHelper->generateConsumerKey());
            $consumer->setData(self::DATA_SECRET, $this->_oauthHelper->generateConsumerSecret());
            $this->_setFormData($consumer->getData());
        }

        $this->_registry->register(self::REGISTRY_KEY_CURRENT_CONSUMER, $consumer->getData());

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Edit consumer action
     */
    public function editAction()
    {
        $consumerId = (int)$this->getRequest()->getParam(self::PARAM_CONSUMER_ID);
        $consumer = $this->_fetchConsumer($consumerId);

        $consumer->addData($this->_filter($this->getRequest()->getParams()));
        $this->_registry->register(self::REGISTRY_KEY_CURRENT_CONSUMER, $consumer->getData());

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Redirect either to edit an existing consumer or to add a new consumer.
     *
     * @param int|null $consumerId - A consumer id.
     */
    private function _redirectToEditOrNew($consumerId)
    {
        if ($consumerId) {
            $this->_redirect('*/*/edit', array(self::PARAM_CONSUMER_ID => $consumerId));
        } else {
            $this->_redirect('*/*/new');
        }
    }

    /**
     * Save consumer action
     */
    public function saveAction()
    {
        $consumerId = $this->getRequest()->getParam(self::PARAM_CONSUMER_ID);
        if (!$this->_validateFormKey()) {
            $this->_redirectToEditOrNew($consumerId);
            return;
        }

        $data = $this->_filter($this->getRequest()->getParams());

        if ($consumerId) {
            $data = array_merge($this->_fetchConsumer($consumerId)->getData(), $data);
        } else {
            $dataForm = $this->_getFormData();
            if ($dataForm) {
                $data[self::DATA_KEY] = $dataForm[self::DATA_KEY];
                $data[self::DATA_SECRET] = $dataForm[self::DATA_SECRET];
            } else {
                // If an admin started to create a new consumer and at this moment he has been edited an existing
                // consumer, we save the new consumer with a new key-secret pair
                $data[self::DATA_KEY] = $this->_oauthHelper->generateConsumerKey();
                $data[self::DATA_SECRET] = $this->_oauthHelper->generateConsumerSecret();
            }
        }

        $verifier = array();
        try {
            $verifier = $this->_oauthService->postToConsumer($this->_oauthService->createConsumer($data));
            $this->_getSession()->addSuccess(__('The add-on has been saved.'));
            $this->_setFormData(null);
        } catch (Magento_Core_Exception $e) {
            $this->_setFormData($data);
            $this->_getSession()->addError($this->_oauthHelper->escapeHtml($e->getMessage()));
            $this->getRequest()->setParam('back', 'edit');
        } catch (Exception $e) {
            $this->_setFormData(null);
            $this->_getSession()->addError(__('An error occurred on saving add-on data.'));
        }

        if ($this->getRequest()->getParam('back')) {
            $this->_redirectToEditOrNew($consumerId);
        } else if ($verifier['oauth_verifier']) {
            //$this->_redirect('<Add-On Website URL>', array(
                    //'oauth_consumer_key' => $data[self::DATA_KEY],
                    //'oauth_verifier' => $verifier['oauth_verifier'],
                    //'return_url' => $this->getUrl('*/*/index')
                //));
            $this->_redirect('*/*/index');
        } else {
            $this->_redirect('*/*/index');
        }
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        $action = $this->getRequest()->getActionName();
        $resourceId = null;

        switch ($action) {
            case 'delete':
                $resourceId = 'Magento_Oauth::consumer_delete';
                break;
            case 'new':
            case 'save':
                $resourceId = 'Magento_Oauth::consumer_edit';
                break;
            default:
                $resourceId = 'Magento_Oauth::consumer';
                break;
        }

        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Get form data
     *
     * @return array
     */
    protected function _getFormData()
    {
        return $this->_getSession()->getData(self::SESSION_KEY_CONSUMER_DATA, true);
    }

    /**
     * Set form data
     *
     * @param $data
     * @return Magento_Oauth_Adminhtml_Oauth_ConsumerController
     */
    protected function _setFormData($data)
    {
        $this->_getSession()->setData(self::SESSION_KEY_CONSUMER_DATA, $data);
        return $this;
    }

    /**
     * Delete consumer action
     */
    public function deleteAction()
    {
        $consumerId = (int) $this->getRequest()->getParam(self::PARAM_CONSUMER_ID);
        if ($consumerId) {
            try {
                $this->_fetchConsumer($consumerId)->delete();
                $this->_getSession()->addSuccess(__('The add-on has been deleted.'));
            } catch (Magento_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()
                    ->addException($e, __('An error occurred while deleting the add-on.'));
            }
        }
        $this->_redirect('*/*/index');
    }
}
