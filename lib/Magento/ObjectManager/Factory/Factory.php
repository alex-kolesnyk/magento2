<?php
/**
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */
class Magento_ObjectManager_Factory_Factory implements Magento_ObjectManager_Factory
{
    /**
     * @var Magento_ObjectManager_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Magento_ObjectManager_Config
     */
    protected $_config;

    /**
     * Definition list
     *
     * @var Magento_ObjectManager_Definition
     */
    protected $_definitions;

    /**
     * List of classes being created
     *
     * @var array
     */
    protected $_creationStack = array();

    /**
     * Application init arguments
     *
     * @var array
     */
    protected $_globalArguments = array();

    /**
     * @param Magento_ObjectManager_Config $config
     * @param Magento_ObjectManager_ObjectManager $objectManager
     * @param Magento_ObjectManager_Definition $definitions
     * @param array $globalArguments
     */
    public function __construct(
        Magento_ObjectManager_Config $config,
        Magento_ObjectManager_ObjectManager $objectManager = null,
        Magento_ObjectManager_Definition $definitions = null,
        $globalArguments = array()
    ) {
        $this->_objectManager = $objectManager;
        $this->_config = $config;
        $this->_definitions = $definitions ?: new Magento_ObjectManager_Definition_Runtime();
        $this->_globalArguments = $globalArguments;
    }

    /**
     * Resolve constructor arguments
     *
     * @param string $requestedType
     * @param array $parameters
     * @param array $arguments
     * @return array
     * @throws LogicException
     * @throws BadMethodCallException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _resolveArguments($requestedType, array $parameters, array $arguments = array())
    {
        $resolvedArguments = array();
        $arguments = $this->_config->getArguments($requestedType, $arguments);
        foreach ($parameters as $parameter) {
            list($paramName, $paramType, $paramRequired, $paramDefault) = $parameter;
            $argument = null;
            if (array_key_exists($paramName, $arguments)) {
                $argument = $arguments[$paramName];
            } else if ($paramRequired) {
                if ($paramType) {
                    $argument = array('instance' => $paramType);
                } else {
                    $this->_creationStack = array();
                    throw new BadMethodCallException(
                        'Missing required argument $' . $paramName . ' for ' . $requestedType . '.'
                    );
                }
            } else {
                $argument = $paramDefault;
            }
            if ($paramType && !is_object($argument) && $argument !== $paramDefault) {
                if (!is_array($argument) || !isset($argument['instance'])) {
                    $this->_creationStack = array();
                    throw new InvalidArgumentException(
                        'Invalid parameter configuration provided for $' . $paramName . ' argument in ' . $requestedType
                    );
                }
                $argumentType = $argument['instance'];
                if (isset($this->_creationStack[$argumentType])) {
                    $this->_creationStack = array();
                    throw new LogicException(
                        'Circular dependency: ' . $argumentType . ' depends on ' . $requestedType . ' and viceversa.'
                    );
                }
                $this->_creationStack[$requestedType] = 1;

                $isShared = (!isset($argument['shared']) && $this->_config->isShared($argumentType))
                    || (isset($argument['shared']) && $argument['shared']);
                $argument = $isShared
                    ? $this->_objectManager->get($argumentType)
                    : $this->_objectManager->create($argumentType);
                unset($this->_creationStack[$requestedType]);
            } else if (is_array($argument) && isset($argument['argument'])) {
                $argKey = $argument['argument'];
                $argument = isset($this->_globalArguments[$argKey]) ? $this->_globalArguments[$argKey] : $paramDefault;
            }
            $resolvedArguments[] = $argument;
        }
        return $resolvedArguments;
    }

    /**
     * Set object manager
     *
     * @param Magento_ObjectManager $objectManager
     */
    public function setObjectManager(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create instance with call time arguments
     *
     * @param string $requestedType
     * @param array $arguments
     * @return object
     * @throws LogicException
     * @throws BadMethodCallException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function create($requestedType, array $arguments = array())
    {
        $type = $this->_config->getInstanceType($requestedType);
        $parameters = $this->_definitions->getParameters($type);
        if ($parameters == null) {
            return new $type();
        }
        $args = $this->_resolveArguments($requestedType, $parameters, $arguments);

        switch(count($args)) {
            case 1:
                return new $type($args[0]);

            case 2:
                return new $type($args[0], $args[1]);

            case 3:
                return new $type($args[0], $args[1], $args[2]);

            case 4:
                return new $type($args[0], $args[1], $args[2], $args[3]);

            case 5:
                return new $type($args[0], $args[1], $args[2], $args[3], $args[4]);

            case 6:
                return new $type($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);

            case 7:
                return new $type($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);

            case 8:
                return new $type($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]);

            default:
                $reflection = new \ReflectionClass($type);
                return $reflection->newInstanceArgs($args);
        }
    }
}
