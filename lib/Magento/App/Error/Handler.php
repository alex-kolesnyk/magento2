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
    protected $_logger;

    /**
     * @var \Magento\App\Dir
     */
    protected $_dir;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @param \Magento\Logger $logger
     * @param \Magento\App\Dir $dir
     * @param \Magento\App\State $appState
     */
    public function __construct(
        \Magento\Logger $logger,
        \Magento\App\Dir $dir,
        \Magento\App\State $appState
    ) {
        $this->_logger = $logger;
        $this->_dir = $dir;
        $this->_appState = $appState;
    }

    /**
     * Process exception
     *
     * @param \Exception $exception
     * @param array $params
     */
    public function processException(\Exception $exception, array $params = array())
    {
        if ($this->_appState->getMode() == \Magento\App\State::MODE_DEVELOPER) {
            parent::processException($exception, $params);
        } else {
            $reportData = array($exception->getMessage(), $exception->getTraceAsString()) + $params;
            // retrieve server data
            if (isset($_SERVER)) {
                if (isset($_SERVER['REQUEST_URI'])) {
                    $reportData['url'] = $_SERVER['REQUEST_URI'];
                }
                if (isset($_SERVER['SCRIPT_NAME'])) {
                    $reportData['script_name'] = $_SERVER['SCRIPT_NAME'];
                }
            }
            require_once($this->_dir->getDir(\Magento\App\Dir::PUB) . DS . 'errors' . DS . 'report.php');
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
        if ($this->_appState->getMode() == \Magento\App\State::MODE_DEVELOPER) {
            parent::_processError($errorMessage);
        } else {
            $this->_logger->log($errorMessage, \Zend_Log::ERR);
        }
    }
}
