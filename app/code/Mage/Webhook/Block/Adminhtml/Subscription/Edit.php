<?php
/**
 * Container for editing subscription grid
 *
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Webhook_Block_Adminhtml_Subscription_Edit extends Mage_Backend_Block_Widget_Form_Container
{
    /** Key used to store subscription data into the registry */
    const REGISTRY_KEY_CURRENT_SUBSCRIPTION = 'current_subscription';

    /** Keys used to retrieve values from subscription data array */
    const DATA_SUBSCRIPTION_ID = 'subscription_id';

    /** @var array $_subscriptionData */
    protected $_subscriptionData;

    /**
     * @param Mage_Core_Model_Registry $registry
     * @param Mage_Backend_Block_Template_Context $context
     * @param array $data
     */
    public function __construct(
        Mage_Core_Model_Registry $registry,
        Mage_Backend_Block_Template_Context $context,
        array $data = array()
    ) {
        parent::__construct($context, $data);

        $this->_objectId = 'id';
        $this->_blockGroup = 'Mage_Webhook';
        $this->_controller = 'adminhtml_subscription';
        $this->_subscriptionData = $registry->registry(self::REGISTRY_KEY_CURRENT_SUBSCRIPTION);
        // Don't allow the merchant to delete subscriptions that were generated by config file.
        if ($this->_isCreatedByConfig()) {
            $this->_removeButton('delete');
        }
    }

    /**
     * Gets header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->_isExistingSubscription()) {
            return __('Edit Subscription');
        } else {
            return __('Add Subscription');
        }
    }

    /**
     * Returns true is subscription exists
     *
     * @return bool
     */
    protected function _isExistingSubscription()
    {
        return $this->_subscriptionData
            && isset($this->_subscriptionData[self::DATA_SUBSCRIPTION_ID])
            && $this->_subscriptionData[self::DATA_SUBSCRIPTION_ID];
    }

    /**
     * Check whether subscription was generated from configuration.
     * 
     * Return false if subscription created within UI.
     *
     * @return bool
     */
    protected function _isCreatedByConfig()
    {
        return $this->_subscriptionData && isset($this->_subscriptionData['alias']);
    }
}
