<?php
/**
 * {license_notice}
 *
 * @category   Magento
 * @package    Tools
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Db adapters factory
 */
namespace Magento\Tools\Migration\Acl\Db\Adapter;

class Factory
{
    /**
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get db adapter
     *
     * @param array $config
     * @param string $type
     * @throws \InvalidArgumentException
     * @return \Zend_Db_Adapter_Abstract
     */
    public function getAdapter(array $config, $type = null)
    {
        $dbAdapterClassName = 'Magento\Db\Adapter\Pdo\Mysql';

        if (false == empty($type)) {
            $dbAdapterClassName = $type;
        }

        if (false == class_exists($dbAdapterClassName, true)) {
            throw new \InvalidArgumentException('Specified adapter not exists: ' . $dbAdapterClassName);
        }

        $adapter = $this->_objectManager->create($dbAdapterClassName, array('config' => $config));
        if (false == ($adapter instanceof \Zend_Db_Adapter_Abstract)) {
            unset($adapter);
            throw new \InvalidArgumentException('Specified adapter is not instance of \Zend_Db_Adapter_Abstract');
        }
        return $adapter;
    }
}
