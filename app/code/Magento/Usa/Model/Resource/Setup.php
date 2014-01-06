<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Usa
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Usa\Model\Resource;


class Setup extends \Magento\Core\Model\Resource\Setup
{
    /**
     * @var \Magento\Core\Model\Locale
     */
    protected $_localeModel;

    /**
     * @param \Magento\Core\Model\Resource\Setup\Context $context
     * @param string $resourceName
     * @param string $moduleName
     * @param \Magento\Core\Model\Locale $localeModel
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Core\Model\Resource\Setup\Context $context,
        $resourceName,
        $moduleName,
        \Magento\Core\Model\Locale $localeModel,
        $connectionName = ''
    )
    {
        $this->_localeModel = $localeModel;
        parent::__construct($context, $resourceName, $moduleName, $connectionName);
    }

    /**
     * @return \Magento\Core\Model\Locale
     */
    public function getLocale()
    {
        return $this->_localeModel;
    }
}