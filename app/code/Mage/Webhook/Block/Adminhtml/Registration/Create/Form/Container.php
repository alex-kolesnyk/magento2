<?php
/**
 * Container for "create registration" form
 *
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Webhook_Block_Adminhtml_Registration_Create_Form_Container extends Mage_Backend_Block_Template
{
    /** Key used to store subscription data into the registry */
    const REGISTRY_KEY_CURRENT_SUBSCRIPTION = 'current_subscription';

    /** Keys used to retrieve values from subscription data array */
    const DATA_SUBSCRIPTION_ID = 'subscription_id';
    const DATA_NAME = 'name';

    /** @var array */
    protected $_subscriptionData;

    /**
     * @param Mage_Core_Model_Registry $registry
     * @param Mage_Backend_Block_Template_Context $context
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Block_Template_Context $context,
        Mage_Core_Model_Registry $registry,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_subscriptionData = $registry->registry(self::REGISTRY_KEY_CURRENT_SUBSCRIPTION);;
    }

    /**
     * Gets submit url
     *
     * @return string Form url
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/register', array('id' => $this->_subscriptionData[self::DATA_SUBSCRIPTION_ID]));
    }

    /**
     * Get subscription name
     *
     * @return string
     */
    public function getSubscriptionName()
    {
        return $this->_subscriptionData[self::DATA_NAME];
    }
}
