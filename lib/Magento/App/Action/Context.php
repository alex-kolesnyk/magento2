<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\App\Action;

class Context implements \Magento\ObjectManager\ContextInterface
{
    /**
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Core\Model\Session\AbstractSession
     */
    protected $_session;

    /**
     * @var \Magento\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\App\Request\Redirect
     */
    protected $_redirect;

    /**
     * @var \Magento\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var \Magento\View\Action\LayoutServiceInterface
     */
    protected $_layoutServices;

    /**
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\App\ResponseInterface $response
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\App\FrontController $frontController
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\Session\AbstractSession $session
     * @param \Magento\UrlInterface $url
     * @param \Magento\App\Request\Redirect $redirect
     * @param \Magento\App\ActionFlag $actionFlag
     * @param \Magento\View\Action\LayoutServiceInterface $layoutService
     */
    public function __construct(
        \Magento\App\RequestInterface $request,
        \Magento\App\ResponseInterface $response,
        \Magento\ObjectManager $objectManager,
        \Magento\App\FrontController $frontController,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\Session\AbstractSession $session,
        \Magento\UrlInterface $url,
        \Magento\App\Request\Redirect $redirect,
        \Magento\App\ActionFlag $actionFlag,
        \Magento\View\Action\LayoutServiceInterface $layoutService
    ) {
        $this->_request = $request;
        $this->_response = $response;
        $this->_objectManager = $objectManager;
        $this->_eventManager = $eventManager;
        $this->_session = $session;
        $this->_url = $url;
        $this->_redirect = $redirect;
        $this->_actionFlag = $actionFlag;
        $this->_layoutServices = $layoutService;
    }

    /**
     * @return \Magento\App\ActionFlag
     */
    public function getActionFlag()
    {
        return $this->_actionFlag;
    }

    /**
     * @return \Magento\Event\ManagerInterface
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * @return \Magento\View\Action\LayoutServiceInterface
     */
    public function getLayoutServices()
    {
        return $this->_layoutServices;
    }

    /**
     * @return \Magento\ObjectManager
     */
    public function getObjectManager()
    {
        return $this->_objectManager;
    }

    /**
     * @return \Magento\App\Request\Redirect
     */
    public function getRedirect()
    {
        return $this->_redirect;
    }

    /**
     * @return \Magento\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @return \Magento\App\ResponseInterface
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @return \Magento\Core\Model\Session\AbstractSession
     */
    public function getSession()
    {
        return $this->_session;
    }

    /**
     * @return \Magento\UrlInterface
     */
    public function getUrl()
    {
        return $this->_url;
    }
}
