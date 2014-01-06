<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Usa
 * @copyright   {copyright}
 * @license     {license_link}
 */

$days = $this->getLocale()->getTranslationList('days');

$days = array_keys($days['format']['wide']);
foreach ($days as $key => $value) {
    $days[$key] = ucfirst($value);
}

$select = $this->getConnection()
    ->select()
    ->from($this->getTable('core_config_data'), array('config_id', 'value'))
    ->where('path = ?', 'carriers/dhl/shipment_days')
    ->orWhere('path = ?', 'carriers/dhl/intl_shipment_days');

foreach ($this->getConnection()->fetchAll($select) as $configRow) {
    $row = array('value' => implode(',', array_intersect_key($days, array_flip(explode(',', $configRow['value'])))));
    $this->getConnection()->update(
        $this->getTable('core_config_data'),
        $row,
        array(
            'config_id = ?' => $configRow['config_id']
        )
    );
}
