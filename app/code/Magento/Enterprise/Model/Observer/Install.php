<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Enterprise
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Installer observer
 *
 */
namespace Magento\Enterprise\Model\Observer;

class Install
{
    /**
     * @var Magento_Install_Model_Installer
     */
    protected $_installer;

    /**
     * @param Magento_Install_Model_Installer $installer
     */
    public function __construct(
        Magento_Install_Model_Installer $installer
    ) {
        $this->_installer = $installer;
    }

    /**
     * Set Enterprise design theme and flag to hide iframe
     *
     * @param \Magento\Event\Observer $observer
     */
    public function setDesignTheme($observer)
    {
        $this->_installer->setHideIframe(true);
    }
}
