<?php
/**
 * Magento object manager. Responsible for instantiating objects taking itno account:
 * - constructor arguments (using configured, and provided parameters)
 * - class instances life style (singleton, transient)
 * - interface preferences
 *
 * Intentionally contains multiple concerns for optimum performance
 *
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */
class Magento_ObjectManager_ObjectManager implements Magento_ObjectManager
{
    /**
     * Class definitions
     *
     * @var Magento_ObjectManager_Definition
     */
    protected $_definitions;

    /**
     * List of configured arguments
     *
     * @var array
     */
    protected $_arguments = array();

    /**
     * Interface preferences
     *
     * @var array
     */
    protected $_preferences = array();

    protected $_nonShared = array();

    /**
     * List of classes being created
     *
     * @var array
     */
    protected $_creationStack = array();

    /**
     * List of shared instances
     *
     * @var array
     */
    protected $_sharedInstances = array();

    /**
     * @param Magento_ObjectManager_Definition $definitions
     * @param array $configuration
     * @param array $sharedInstances
     */
    public function __construct(
        Magento_ObjectManager_Definition $definitions = null,
        array $configuration = array(),
        array $sharedInstances = array()
    ) {
        $this->_definitions = $definitions ?: new Magento_ObjectManager_Definition_Runtime();
        $this->_sharedInstances = $sharedInstances;
        $this->_sharedInstances['Magento_ObjectManager'] = $this;
        $this->configure($configuration);
    }

    /**
     * Resolve constructor arguments
     *
     * @param string $className
     * @param array $parameters
     * @param array $arguments
     * @return array
     * @throws LogicException
     * @throws BadMethodCallException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _resolveArguments($className, array $parameters, array $arguments = array())
    {
        $resolvedArguments = array();
        if (isset($this->_arguments[$className])) {
            $arguments = array_replace($this->_arguments[$className], $arguments);
        }
        foreach ($parameters as $parameter) {
            list($paramName, $paramType, $paramRequired, $paramDefault) = $parameter;
            $argument = null;
            if (array_key_exists($paramName, $arguments)) {
                $argument = $arguments[$paramName];
            } else if ($paramRequired) {
                if ($paramType) {
                    $argument = array('instance' => $paramType);
                } else {
                    throw new BadMethodCallException(
                        'Missing required argument $' . $paramName . ' for ' . $className . '.'
                    );
                }
            } else {
                $argument = $paramDefault;
            }
            if ($paramRequired && $paramType && !is_object($argument)) {
                if (!is_array($argument) || !isset($argument['instance'])) {
                    throw new InvalidArgumentException(
                        'Invalid parameter configuration provided for $' . $paramName . ' argument in ' . $className
                    );
                }
                $instanceName = $argument['instance'];
                if (isset($this->_creationStack[$instanceName])) {
                    throw new LogicException(
                        'Circular dependency: ' . $instanceName . ' depends on ' . $className . ' and viceversa.'
                    );
                }
                $this->_creationStack[$className] = 1;

                $isShared = (!isset($argument['shared']) && !isset($this->_nonShared[$instanceName]))
                    || (isset($argument['shared']) && $argument['shared'] != 'false');
                if (isset($argument['lazy']) && $argument['lazy']) {
                    $instanceType = $this->_resolveInstanceType($instanceName);
                    if (!$isShared) {
                        $argument = $this->_create(
                            $instanceType . '_Proxy',
                            array('shared' => false, 'instanceName' => $instanceName)
                        );
                    } else {
                        if (!isset($this->_proxies[$paramType][$className])) {
                            $this->_proxies[$paramType][$className] = $this->_create(
                                $instanceType . '_Proxy',
                                array('shared' => true, 'instanceName' => $instanceName)
                            );
                        }
                        $argument = $this->_proxies[$paramType][$className];
                    }
                } else if (!$isShared) {
                    $argument = $this->create($instanceName);
                } else {
                    $argument = $this->get($instanceName);
                }
                unset($this->_creationStack[$className]);
            }
            $resolvedArguments[] = $argument;
        }
        return $resolvedArguments;
    }

    /**
     * Resolve Class name
     *
     * @param string $className
     * @return string
     * @throws LogicException
     */
    protected function _resolveClassName($className)
    {
        $preferencePath = array();
        while (isset($this->_preferences[$className])) {
            if (isset($preferencePath[$this->_preferences[$className]])) {
                throw new LogicException(
                    'Circular type preference: ' . $className . ' relates to '
                        . $this->_preferences[$className] . ' and viceversa.'
                );
            }
            $className = $this->_preferences[$className];
            $preferencePath[$className] = 1;
        }
        return $className;
    }

    /**
     * Resolve instance name
     *
     * @param string $instanceName
     * @return string
     */
    protected function _resolveInstanceType($instanceName)
    {
        while(isset($this->_virtualTypes[$instanceName])) {
            $instanceName = $this->_preferences[$instanceName];
        }
        return $instanceName;
    }

    /**
     * Create instance with call time arguments
     *
     * @param string $resolvedClassName
     * @param array $arguments
     * @return object
     * @throws LogicException
     * @throws BadMethodCallException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _create($resolvedClassName, array $arguments = array())
    {
        $type = $this->_resolveInstanceType($resolvedClassName);
        $parameters = $this->_definitions->getParameters($type);
        if ($parameters == null) {
            return new $type();
        }
        $args = $this->_resolveArguments($resolvedClassName, $parameters, $arguments);

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
                return new $type(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]
                );

            case 9:
                return new $type(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8]
                );

            case 10:
                return new $type(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9]
                );

            case 11:
                return new $type(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9],
                    $args[10]
                );

            case 12:
                return new $type(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9],
                    $args[10], $args[11]
                );

            case 13:
                return new $type(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9],
                    $args[10], $args[11], $args[12]
                );

            case 14:
                return new $type(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9],
                    $args[10], $args[11], $args[12], $args[13]
                );

            case 15:
                return new $type(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9],
                    $args[10], $args[11], $args[12], $args[13], $args[14]
                );

            case 16:
                return new $type(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9],
                    $args[10], $args[11], $args[12], $args[13], $args[14], $args[15]
                );

            case 17:
                return new $type(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9],
                    $args[10], $args[11], $args[12], $args[13], $args[14], $args[15], $args[16]
                );

            case 18:
                return new $type(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9],
                    $args[10], $args[11], $args[12], $args[13], $args[14], $args[15], $args[16], $args[17]
                );

            default:
                $reflection = new \ReflectionClass($type);
                return $reflection->newInstanceArgs($args);
        }
    }

    /**
     * Create new object instance
     *
     * @param string $className
     * @param array $arguments
     * @return mixed
     */
    public function create($className, array $arguments = array())
    {
        if (isset($this->_preferences[$className])) {
            $className = $this->_resolveClassName($className);
        }
        return $this->_create($className, $arguments);
    }

    /**
     * Retrieve cached object instance
     *
     * @param string $className
     * @return mixed
     */
    public function get($className)
    {
        if (isset($this->_preferences[$className])) {
            $className = $this->_resolveClassName($className);
        }
        if (!isset($this->_sharedInstances[$className])) {
            $this->_sharedInstances[$className] = $this->_create($className);
        }
        return $this->_sharedInstances[$className];
    }

    /**
     * Configure di instance
     *
     * @param array $configuration
     */
    public function configure(array $configuration)
    {
        foreach ($configuration as $key => $curConfig) {
            switch ($key) {
                case 'preferences':
                    $this->_preferences = array_replace($this->_preferences, $curConfig);
                    break;

                default:
                    if (isset($curConfig['type'])) {
                        $this->_virtualTypes[$key] = $curConfig['type'];
                    }
                    if (isset($curConfig['parameters'])) {
                        if (isset($this->_arguments[$key])) {
                            $this->_arguments[$key] = array_replace($this->_arguments[$key], $curConfig['parameters']);
                        } else {
                            $this->_arguments[$key] = $curConfig['parameters'];
                        }
                    }
                    if (isset($curConfig['shared'])) {
                        if (!$curConfig['shared'] || $curConfig['shared'] == 'false') {
                            $this->_nonShared[$key] = 1;
                        } else {
                            unset($this->_nonShared[$key]);
                        }
                    }
                    break;
            }
        }
    }
}
