<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     performance_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Handler for performance testing scenarios in format of Apache JMeter
 */
class Magento_Performance_Scenario_Handler_Jmeter implements Magento_Performance_Scenario_HandlerInterface
{
    /**
     * @var Magento_Shell
     */
    protected $_shell;

    /**
     * @var bool
     */
    protected $_validateExecutable;

    /**
     * Constructor
     *
     * @param Magento_Shell $shell
     * @param bool $validateExecutable
     */
    public function __construct(Magento_Shell $shell, $validateExecutable = true)
    {
        $this->_shell = $shell;
        $this->_validateExecutable = $validateExecutable;
    }

    /**
     * Validate whether scenario executable is available in the environment
     */
    protected function _validateScenarioExecutable()
    {
        if ($this->_validateExecutable) {
            $this->_validateExecutable = false; // validate only once
            $this->_shell->execute('jmeter --version');
        }
    }

    /**
     * Run scenario and optionally write results to report file
     *
     * @param string $scenarioFile
     * @param Magento_Performance_Scenario_Arguments $scenarioArguments
     * @param string|null $reportFile Report file to write results to, NULL disables report creation
     * @throws Magento_Exception
     * @throws Magento_Performance_Scenario_FailureException
     */
    public function run($scenarioFile, Magento_Performance_Scenario_Arguments $scenarioArguments, $reportFile = null)
    {
        $this->_validateScenarioExecutable();
        list($scenarioCmd, $scenarioCmdArgs) = $this->_buildScenarioCmd($scenarioFile, $scenarioArguments, $reportFile);
        $this->_shell->execute($scenarioCmd, $scenarioCmdArgs);
        if ($reportFile) {
            if (!file_exists($reportFile)) {
                throw new Magento_Exception("Report file '$reportFile' has not been created.");
            }
            $reportErrors = $this->_getReportErrors($reportFile);
            if ($reportErrors) {
                throw new Magento_Performance_Scenario_FailureException(
                    $scenarioFile, $scenarioArguments, implode(PHP_EOL, $reportErrors)
                );
            }
        }
    }

    /**
     * Build and return scenario execution command and arguments for it
     *
     * @param string $scenarioFile
     * @param Traversable $scenarioArgs
     * @param string|null $reportFile
     * @return array
     */
    protected function _buildScenarioCmd($scenarioFile, Traversable $scenarioArgs, $reportFile = null)
    {
        $command = 'jmeter -n -t %s';
        $arguments = array($scenarioFile);
        if ($reportFile) {
            $command .= ' -l %s';
            $arguments[] = $reportFile;
        }
        foreach ($scenarioArgs as $key => $value) {
            $command .= ' %s';
            $arguments[] = "-J$key=$value";
        }
        return array($command, $arguments);
    }

    /**
     * Retrieve error/failure messages from the report file
     * @link http://wiki.apache.org/jmeter/JtlTestLog
     *
     * @param string $reportFile
     * @return array
     */
    protected function _getReportErrors($reportFile)
    {
        $result = array();
        $reportXml = simplexml_load_file($reportFile);
        $failedAssertions = $reportXml->xpath(
            '/testResults/*/assertionResult[failure[text()="true"] or error[text()="true"]]'
        );
        if ($failedAssertions) {
            foreach ($failedAssertions as $assertionResult) {
                if (isset($assertionResult->failureMessage)) {
                    $result[] = (string)$assertionResult->failureMessage;
                }
                if (isset($assertionResult->errorMessage)) {
                    $result[] = (string)$assertionResult->errorMessage;
                }
            }
        }
        return $result;
    }
}
