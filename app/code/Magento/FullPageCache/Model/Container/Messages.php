<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_FullPageCache
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Cart sidebar container
 */
namespace Magento\FullPageCache\Model\Container;

class Messages extends \Magento\FullPageCache\Model\Container\AbstractContainer
{
    /**
     * @var \Magento\Stdlib\Cookie
     */
    protected $_coreCookie;

    /**
     * @var \Magento\FullPageCache\Model\Container\MessagesStorageFactory
     */
    protected $_storageFactory;

    /**
     * @var \Zend\Session\Config\ConfigInterface
     */
    protected $_sessionConfig;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\FullPageCache\Model\Cache $fpcCache
     * @param \Magento\FullPageCache\Model\Container\Placeholder $placeholder
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\FullPageCache\Helper\Url $urlHelper
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\Stdlib\Cookie $coreCookie
     * @param \Magento\FullPageCache\Model\Container\MessagesStorageFactory $storageFactory
     * @param \Zend\Session\Config\ConfigInterface $sessionConfig
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\FullPageCache\Model\Cache $fpcCache,
        \Magento\FullPageCache\Model\Container\Placeholder $placeholder,
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\FullPageCache\Helper\Url $urlHelper,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\View\LayoutInterface $layout,
        \Magento\Stdlib\Cookie $coreCookie,
        \Magento\FullPageCache\Model\Container\MessagesStorageFactory $storageFactory,
        \Zend\Session\Config\ConfigInterface $sessionConfig
    ) {
        parent::__construct(
            $eventManager, $fpcCache, $placeholder, $coreRegistry, $urlHelper, $coreStoreConfig, $layout
        );

        $this->_coreCookie = $coreCookie;
        $this->_sessionConfig = $sessionConfig;
        $this->_storageFactory = $storageFactory;
    }

    /**
     * Check for new messages. New message flag will be reseted if needed.
     *
     * @return bool
     */
    protected function _isNewMessageRecived()
    {
        return $this->_getCookieValue(\Magento\FullPageCache\Model\Cookie::COOKIE_MESSAGE)
            || array_key_exists(\Magento\FullPageCache\Model\Cache::REQUEST_MESSAGE_GET_PARAM, $_GET);
    }

    /**
     * Redirect to content processing on new message
     *
     * @param string $content
     * @return bool
     */
    public function applyWithoutApp(&$content)
    {
        if ($this->_isNewMessageRecived()) {
            return false;
        }
        return parent::applyWithoutApp($content);
    }

    /**
     * Render block content
     *
     * @return string
     */
    protected function _renderBlock()
    {
        $this->_coreCookie->set(
            \Magento\FullPageCache\Model\Cookie::COOKIE_MESSAGE,
            null,
            $this->_sessionConfig->getCookieLifetime(),
            $this->_sessionConfig->getCookiePath(),
            $this->_sessionConfig->getCookieDomain()
        );

        $block = $this->_getPlaceHolderBlock();

        // TODO: Getting of storage class name needs to be located in messages storage factory
        $types = unserialize($this->_placeholder->getAttribute('storage_types'));
        foreach ($types as $type) {
            $this->_addMessagesToBlock($type, $block);
        }
        $this->_eventManager->dispatch('render_block', array('block' => $block, 'placeholder' => $this->_placeholder));

        return $block->toHtml();
    }

    /**
     * Add messages from storage to message block
     *
     * @param string $messagesStorage
     * @param \Magento\View\Block\Messages $block
     */
    protected function _addMessagesToBlock($messagesStorage, \Magento\View\Block\Messages $block)
    {
        if ($storage = $this->_storageFactory->get($messagesStorage)) {
            $block->addMessages($storage->getMessages(true));
            $block->setEscapeMessageFlag($storage->getEscapeMessages(true));
        }
    }
}
