<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Rss
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * RSS Controller for Orders feed in Admin
 */
namespace Magento\Rss\Controller\Adminhtml;

class Order extends \Magento\Rss\Controller\Adminhtml\Authenticate
{
    /**
     * New orders action
     */
    public function newAction()
    {
        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
        $this->_layoutServices->loadLayout(false);
        $this->_layoutServices->renderLayout();
    }
}
