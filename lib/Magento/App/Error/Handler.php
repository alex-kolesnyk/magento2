<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Default Error Handler
 */
namespace Magento\App\Error;

class Handler extends \Magento\Error\Handler
{
    /**
     * @var \Magento\Logger
     */
    protected $logger;

    /**
     * @var \Magento\App\Dir
     */
    protected $dir;

    /**
     * @var \Magento\App\State
     */
    protected $appState;

    /**
     * @param \Magento\Logger $logger
     * @param \Magento\App\Dir $dir
     * @param \Magento\App\State $appState
     */
    public function __construct(
        \Magento\Logger $logger,
        \Magento\App\Dir $dir,
        \Magento\App\State $appState
    )
    {
        $this->logger = $logger;
        $this->dir = $dir;
        $this->appState = $appState;
    }


    /**
     * Process exception
     *
     * @param \Exception $exception
     * @param string|null $skinCode
     */
    public function processException(\Exception $exception, $skinCode = null)
    {
        if ($this->appState->getMode() == \Magento\App\State::MODE_DEVELOPER) {
            print '<pre>';
            print $exception->getMessage() . "\n\n";
            print $exception->getTraceAsString();
            print '</pre>';
        } else {
            $reportData = array($exception->getMessage(), $exception->getTraceAsString(), 'skin' => $skinCode);
            // retrieve server data
            if (isset($_SERVER)) {
                if (isset($_SERVER['REQUEST_URI'])) {
                    $reportData['url'] = $_SERVER['REQUEST_URI'];
                }
                if (isset($_SERVER['SCRIPT_NAME'])) {
                    $reportData['script_name'] = $_SERVER['SCRIPT_NAME'];
                }
            }
            require_once($this->dir->getDir(\Magento\App\Dir::PUB) . DS . 'errors' . DS . 'report.php');
        }
    }

    /**
     * Show error as exception or log it
     *
     * @throws \Exception
     */
    protected function _processError($errorMessage)
    {
        $exception = new \Exception($errorMessage);
        $errorMessage .= $exception->getTraceAsString();
        if ($this->appState->getMode() == \Magento\App\State::MODE_DEVELOPER) {
            throw new \Exception($errorMessage);
        } else {
            $this->logger->log($errorMessage, \Zend_Log::ERR);
        }
    }
}
