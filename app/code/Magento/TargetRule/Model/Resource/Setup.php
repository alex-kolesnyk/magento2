<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_TargetRule
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * TargetRule Setup Resource Model
 *
 * @category    Magento
 * @package     Magento_TargetRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\TargetRule\Model\Resource;

class Setup extends \Magento\Catalog\Model\Resource\Setup
{
    /**
     * @param \Magento\Core\Model\Resource\Setup\Context $context
     * @param string $resourceName
     * @param \Magento\App\CacheInterface $cache
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Group\CollectionFactory $attrGrCollFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Index\Model\IndexerFactory $indexerFactory
     * @param \Magento\Catalog\Model\Resource\Eav\AttributeFactory $eavAttributeResourceFactory
     * @param string $moduleName
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Core\Model\Resource\Setup\Context $context,
        $resourceName,
        \Magento\App\CacheInterface $cache,
        \Magento\Eav\Model\Resource\Entity\Attribute\Group\CollectionFactory $attrGrCollFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Index\Model\IndexerFactory $indexerFactory,
        \Magento\Catalog\Model\Resource\Eav\AttributeFactory $eavAttributeResourceFactory,
        $moduleName = 'Magento_TargetRule',
        $connectionName = ''
    ) {
        parent::__construct(
            $context,
            $resourceName,
            $cache,
            $attrGrCollFactory,
            $categoryFactory,
            $indexerFactory,
            $eavAttributeResourceFactory,
            $moduleName,
            $connectionName
        );
    }

    /**
     * Create migration setup
     *
     * @param array $data
     * @return \Magento\Enterprise\Model\Resource\Setup\Migration
     */
    public function createMigrationSetup(array $data = array())
    {
        return $this->_migrationFactory->create($data);
    }
}
