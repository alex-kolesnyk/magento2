<?php
/**
 * Standard profiler driver that uses outputs for displaying profiling results.
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Magento_Profiler_Driver_Standard implements Magento_Profiler_DriverInterface
{
    /**
     * Storage for timers statistics
     *
     * @var Magento_Profiler_Driver_Standard_Stat
     */
    protected $_stat;

    /**
     * List of profiler driver outputs
     *
     * @var Magento_Profiler_Driver_Standard_OutputInterface[]
     */
    protected $_outputs = array();

    /**
     * Constructor
     *
     * @param Magento_Profiler_Driver_Standard_Stat|null $stat
     */
    public function __construct(Magento_Profiler_Driver_Standard_Stat $stat = null)
    {
        if (!$stat) {
            $stat = new Magento_Profiler_Driver_Standard_Stat();
        }
        $this->_stat = $stat;
        register_shutdown_function(array($this, 'display'));
    }

    /**
     * Reset collected statistics for specified timer or for whole profiler if timer name is omitted
     *
     * @param string|null $timerName
     */
    public function reset($timerName = null)
    {
        $this->_stat->reset($timerName);
    }

    /**
     * Start collecting statistics for specified timer
     *
     * @param string $timerName
     * @param array|null $tags
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function start($timerName, array $tags = null)
    {
        $this->_stat->start($timerName, microtime(true), memory_get_usage(true), memory_get_usage());
    }

    /**
     * Stop recording statistics for specified timer.
     *
     * @param string $timerName
     */
    public function stop($timerName)
    {
        $this->_stat->stop($timerName, microtime(true), memory_get_usage(true), memory_get_usage());
    }

    /**
     * Register profiler output instance to display profiling result at the end of execution
     *
     * @param Magento_Profiler_Driver_Standard_OutputInterface $output
     */
    public function registerOutput(Magento_Profiler_Driver_Standard_OutputInterface $output)
    {
        $this->_outputs[] = $output;
    }

    /**
     * Display collected statistics with registered outputs
     */
    public function display()
    {
        if (Magento_Profiler::isEnabled()) {
            foreach ($this->_outputs as $output) {
                $output->display($this->_stat);
            }
        }
    }
}
