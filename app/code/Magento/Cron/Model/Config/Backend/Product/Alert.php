<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Cron
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Backend Model for product alerts
 *
 * @category   Magento
 * @package    Magento_Cron
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Cron\Model\Config\Backend\Product;

class Alert extends \Magento\Core\Model\Config\Value
{
    /**
     * Cron string path
     */
    const CRON_STRING_PATH = 'crontab/jobs/catalog_product_alert/schedule/cron_expr';

    /**
     * Cron model path
     */
    const CRON_MODEL_PATH  = 'crontab/jobs/catalog_product_alert/run/model';

    /**
     * @var \Magento\Core\Model\Config\ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @var string
     */
    protected $_runModelPath = '';

    /**
     * @param \Magento\Core\Model\Config\ValueFactory $configValueFactory
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\Config $config
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param string $runModelPath
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Config\ValueFactory $configValueFactory,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Config $config,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        $runModelPath = '',
        array $data = array()
    ) {
        $this->_runModelPath = $runModelPath;
        $this->_configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $storeManager, $config, $resource, $resourceCollection, $data);
    }

    /**
     * @return \Magento\Core\Model\AbstractModel|void
     * @throws \Exception
     */
    protected function _afterSave()
    {
        $time = $this->getData('groups/productalert_cron/fields/time/value');
        $frequency = $this->getData('groups/productalert_cron/fields/frequency/value');

        $cronExprArray = array(
            intval($time[1]), //Minute
            intval($time[0]), //Hour
            ($frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY) ? '1' : '*', //Day of the Month
            '*', //Month of the Year
            ($frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY) ? '1' : '*', //Day of the Week
        );

        $cronExprString = join(' ', $cronExprArray);

        try {
            $this->_configValueFactory->create()
                ->load(self::CRON_STRING_PATH, 'path')
                ->setValue($cronExprString)
                ->setPath(self::CRON_STRING_PATH)
                ->save();
            $this->_configValueFactory->create()
                ->load(self::CRON_MODEL_PATH, 'path')
                ->setValue($this->_runModelPath)
                ->setPath(self::CRON_MODEL_PATH)
                ->save();
        } catch (\Exception $e) {
            throw new \Exception(__('We can\'t save the cron expression.'));
        }
    }
}
