<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Generic frontend controller
 */
namespace Magento\Core\App\Action\Plugin;

class LastUrl
{
    /**
     * Session namespace to refer in other places
     */
    const SESSION_NAMESPACE = 'frontend';

    /**
     * @var \Magento\Core\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Core\Model\Url
     */
    protected $_url;

    /**
     * Namespace for session.
     *
     * @var string
     */
    protected $_sessionNamespace = self::SESSION_NAMESPACE;

    /**
     * @param \Magento\Core\Model\Session $session
     * @param \Magento\Core\Model\Url $url
     */
    public function __construct(\Magento\Core\Model\Session $session, \Magento\Core\Model\Url $url)
    {
        $this->_session = $session;
        $this->_url = $url;
    }

    /**
     * Process request
     *
     * @param array $arguments
     * @param \Magento\Code\Plugin\InvocationChain $invocationChain
     * @return mixed
     */
    public function aroundDispatch(array $arguments, \Magento\Code\Plugin\InvocationChain $invocationChain)
    {
        $result = $invocationChain->proceed($arguments);
        $this->_session->setLastUrl($this->_url->getUrl('*/*/*', array('_current' => true)));
        return $result;
    }
}
