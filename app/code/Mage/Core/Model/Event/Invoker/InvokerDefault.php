<?php
/**
 * Default event invoker
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Core_Model_Event_Invoker_InvokerDefault implements Mage_Core_Model_Event_InvokerInterface
{
    /**
     * Observer model factory
     *
     * @var Mage_Core_Model_ObserverFactory
     */
    protected $_observerFactory;

    /**
     * Application state
     *
     * @var Mage_Core_Model_App_State
     */
    protected $_appState;

    /**
     * @param Mage_Core_Model_ObserverFactory $observerFactory
     * @param Mage_Core_Model_App_State $appState
     */
    public function __construct(Mage_Core_Model_ObserverFactory $observerFactory, Mage_Core_Model_App_State $appState)
    {
        $this->_observerFactory = $observerFactory;
        $this->_appState = $appState;
    }

    /**
     * Dispatch event
     *
     * @param array $configuration
     * @param Magento_Event_Observer $observer
     */
    public function dispatch(array $configuration, Magento_Event_Observer $observer)
    {
        /** Check whether event observer is disabled */
        if (isset($configuration['disabled']) && true === $configuration['disabled']) {
            return;
        }

        if (isset($configuration['shared']) && false === $configuration['shared']) {
            $object = $this->_observerFactory->create($configuration['instance']);
        } else {
            $object = $this->_observerFactory->get($configuration['instance']);
        }
        $this->_callObserverMethod($object, $configuration['method'], $observer);
    }

    /**
     * Performs non-existent observer method calls protection
     *
     * @param object $object
     * @param string $method
     * @param Magento_Event_Observer $observer
     * @return Mage_Core_Model_Event_InvokerInterface
     * @throws Mage_Core_Exception
     */
    protected function _callObserverMethod($object, $method, $observer)
    {
        if (method_exists($object, $method)) {
            $object->$method($observer);
        } elseif ($this->_appState->getMode() == Mage_Core_Model_App_State::MODE_DEVELOPER) {
            Mage::throwException('Method "' . $method . '" is not defined in "' . get_class($object) . '"');
        }
        return $this;
    }
}
