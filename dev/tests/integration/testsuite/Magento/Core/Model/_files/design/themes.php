<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */
\Mage::app()->loadAreaPart(\Magento\Core\Model\App\Area::AREA_ADMINHTML, \Magento\Core\Model\App\Area::PART_CONFIG);
/** @var $registration \Magento\Core\Model\Theme\Registration */
$registration = \Mage::getModel('Magento\Core\Model\Theme\Registration');
$registration->register(
    __DIR__,
    implode(DIRECTORY_SEPARATOR, array('*', '*', 'theme.xml'))
);
