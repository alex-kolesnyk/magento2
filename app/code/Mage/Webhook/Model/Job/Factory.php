<?php
/**
 * Factory for Mage_Webhook_Model_Job
 *
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Webhook_Model_Job_Factory implements Magento_PubSub_Job_FactoryInterface
{
    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * Initialize the class
     *
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create Job
     *
     * @param Magento_PubSub_SubscriptionInterface $subscription
     * @param Magento_PubSub_EventInterface $event
     * @return Magento_PubSub_JobInterface
     */
    public function create(Magento_PubSub_SubscriptionInterface $subscription, Magento_PubSub_EventInterface $event)
    {
        return $this->_objectManager->create('Mage_Webhook_Model_Job', array(
            'data' => array(
                'event' => $event,
                'subscription' => $subscription,
                'status' => Magento_PubSub_JobInterface::READY_TO_SEND
            )
        ));
    }
}
