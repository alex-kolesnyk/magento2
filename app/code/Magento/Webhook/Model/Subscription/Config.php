<?php
/**
 * Configures subscriptions based on information from config object
 *
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Magento_Webhook_Model_Subscription_Config
{
    /** Webhook subscription configuration path */
    const XML_PATH_SUBSCRIPTIONS = 'global/webhook/subscriptions';

    /** @var Magento_Webhook_Model_Resource_Subscription_Collection  */
    protected $_subscriptionSet;

    /** @var  Magento_Core_Model_Config */
    protected $_mageConfig;

    /** @var  Magento_Webhook_Model_Subscription_Factory */
    protected $_subscriptionFactory;

    /** @var Magento_Core_Model_Logger */
    private $_logger;

    /**
     * @param Magento_Webhook_Model_Resource_Subscription_Collection $subscriptionSet
     * @param Magento_Core_Model_Config $mageConfig
     * @param Magento_Webhook_Model_Subscription_Factory $subscriptionFactory
     * @param Magento_Core_Model_Logger $logger
     */
    public function __construct(
        Magento_Webhook_Model_Resource_Subscription_Collection $subscriptionSet,
        Magento_Core_Model_Config $mageConfig,
        Magento_Webhook_Model_Subscription_Factory $subscriptionFactory,
        Magento_Core_Model_Logger $logger
    ) {
        $this->_subscriptionSet = $subscriptionSet;
        $this->_mageConfig = $mageConfig;
        $this->_subscriptionFactory = $subscriptionFactory;
        $this->_logger = $logger;
    }

    /**
     * Checks if new subscriptions need to be generated from config files
     *
     * @return Magento_Webhook_Model_Subscription_Config
     */
    public function updateSubscriptionCollection()
    {
        $subscriptionConfig = $this->_mageConfig->getNode(self::XML_PATH_SUBSCRIPTIONS);

        if (!empty($subscriptionConfig)) {
            $subscriptionConfig = $subscriptionConfig->asArray();
        }
        // It could be no subscriptions have been defined
        if (!$subscriptionConfig) {
            return $this;
        }

        foreach ($subscriptionConfig as $alias => $subscriptionData) {
            try {
                $this->_validateConfigData($subscriptionData, $alias);
                $subscriptions = $this->_subscriptionSet->getSubscriptionsByAlias($alias);
                if (empty($subscriptions)) {
                    // add new subscription
                    $subscription = $this->_subscriptionFactory->create()
                        ->setAlias($alias)
                        ->setStatus(Magento_Webhook_Model_Subscription::STATUS_INACTIVE);
                } else {
                    // get first subscription from array
                    $subscription = current($subscriptions);
                }

                // update subscription from config
                $this->_updateSubscriptionFromConfigData($subscription, $subscriptionData);
            } catch (LogicException $e){
                $this->_logger->logException(new Magento_Webhook_Exception($e->getMessage()));
            }
        }
        return $this;
    }

    /**
     * Validates config data by checking that $data is an array and that 'data' maps to some value
     *
     * @param mixed $data
     * @param string $alias
     * @throws LogicException
     */
    protected function _validateConfigData($data, $alias)
    {
        //  We can't demand that every possible value be supplied as some of these can be supplied
        //  at a later point in time using the web API
        if (!( is_array($data) && isset($data['name']))) {
            throw new LogicException(__(
                "Invalid config data for subscription '%1'.", $alias
            ));
        }
    }

    /**
     * Configures a subscription
     *
     * @param Magento_Webhook_Model_Subscription $subscription
     * @param array $rawConfigData
     * @return Magento_Core_Model_Abstract
     */
    protected function _updateSubscriptionFromConfigData(
        Magento_Webhook_Model_Subscription $subscription,
        array $rawConfigData
    ) {
        // Set defaults for unset values
        $configData = $this->_processConfigData($rawConfigData);

        $subscription->setName($configData['name'])
            ->setFormat($configData['format'])
            ->setEndpointUrl($configData['endpoint_url'])
            ->setTopics($configData['topics'])
            ->setAuthenticationType($configData['authentication_type'])
            ->setRegistrationMechanism($configData['registration_mechanism']);

        return $subscription->save();
    }

    /**
     * Sets defaults for unset values
     *
     * @param array $configData
     * @return array
     */
    private function _processConfigData($configData)
    {
        $defaultData = array(
            'name' => null,
            'format' => Magento_Outbound_EndpointInterface::FORMAT_JSON,
            'endpoint_url' => null,
            'topics' => array(),
            'authentication_type' => Magento_Outbound_EndpointInterface::AUTH_TYPE_NONE,
            'registration_mechanism' => Magento_Webhook_Model_Subscription::REGISTRATION_MECHANISM_MANUAL,
        );

        if (isset($configData['topics'])) {
            $configData['topics'] = $this->_getTopicsFlatList($configData['topics']);
        }

        return array_merge($defaultData, $configData);
    }

    /**
     * Convert topics into acceptable form for subscription
     *
     * @param array $topics
     * @return array
     */
    protected function _getTopicsFlatList(array $topics)
    {
        $flatList = array();

        foreach ($topics as $topicGroup => $topicNames) {
            $topicNamesKeys = array_keys($topicNames);
            foreach ($topicNamesKeys as $topicName) {
                $flatList[] = $topicGroup . '/' . $topicName;
            }
        }

        return $flatList;
    }
}
