<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Sendfriend
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Email to a Friend Block
 *
 * @category    Magento
 * @package     Magento_Sendfriend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Sendfriend_Block_Send extends Magento_Core_Block_Template
{
    /**
     * Sendfriend data
     *
     * @var Magento_Sendfriend_Helper_Data
     */
    protected $_sendfriendData = null;

    /**
     * Core registry
     *
     * @var Magento_Core_Model_Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var Magento_Customer_Model_Session
     */
    protected $_customerSession;

    /**
     * @param Magento_Customer_Model_Session $customerSession
     * @param Magento_Sendfriend_Helper_Data $sendfriendData
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Core_Block_Template_Context $context
     * @param Magento_Core_Model_Registry $registry
     * @param array $data
     */
    public function __construct(
        Magento_Customer_Model_Session $customerSession,
        Magento_Sendfriend_Helper_Data $sendfriendData,
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Block_Template_Context $context,
        Magento_Core_Model_Registry $registry,
        array $data = array()
    ) {
        $this->_customerSession = $customerSession;
        $this->_coreRegistry = $registry;
        $this->_sendfriendData = $sendfriendData;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Retrieve username for form field
     *
     * @return string
     */
    public function getUserName()
    {
        $name = $this->getFormData()->getData('sender/name');
        if (!empty($name)) {
            return trim($name);
        }

        /* @var $session Magento_Customer_Model_Session */
        $session = $this->_customerSession;

        if ($session->isLoggedIn()) {
            return $session->getCustomer()->getName();
        }

        return '';
    }

    /**
     * Retrieve sender email address
     *
     * @return string
     */
    public function getEmail()
    {
        $email = $this->getFormData()->getData('sender/email');
        if (!empty($email)) {
            return trim($email);
        }

        /* @var $session Magento_Customer_Model_Session */
        $session = $this->_customerSession;

        if ($session->isLoggedIn()) {
            return $session->getCustomer()->getEmail();
        }

        return '';
    }

    /**
     * Retrieve Message text
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->getFormData()->getData('sender/message');
    }

    /**
     * Retrieve Form data or empty Magento_Object
     *
     * @return Magento_Object
     */
    public function getFormData()
    {
        $data = $this->getData('form_data');
        if (!$data instanceof Magento_Object) {
            $data = new Magento_Object();
            $this->setData('form_data', $data);
        }

        return $data;
    }

    /**
     * Set Form data array
     *
     * @param array $data
     * @return Magento_Sendfriend_Block_Send
     */
    public function setFormData($data)
    {
        if (is_array($data)) {
            $this->setData('form_data', new Magento_Object($data));
        }

        return $this;
    }

    /**
     * Retrieve Current Product Id
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->getRequest()->getParam('id', null);
    }

    /**
     * Retrieve current category id for product
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->getRequest()->getParam('cat_id', null);
    }

    /**
     * Retrieve Max Recipients
     *
     * @return int
     */
    public function getMaxRecipients()
    {
        return $this->_sendfriendData->getMaxRecipients();
    }

    /**
     * Retrieve Send URL for Form Action
     *
     * @return string
     */
    public function getSendUrl()
    {
        return $this->getUrl('*/*/sendmail', array(
            'id'     => $this->getProductId(),
            'cat_id' => $this->getCategoryId()
        ));
    }

    /**
     * Return send friend model
     *
     * @return Magento_Sendfriend_Model_Sendfriend
     */
    protected function _getSendfriendModel()
    {
        return $this->_coreRegistry->registry('send_to_friend_model');
    }

    /**
     * Check if user is allowed to send
     *
     * @return boolean
     */
    public function canSend()
    {
        return !$this->_getSendfriendModel()->isExceedLimit();
    }
}
