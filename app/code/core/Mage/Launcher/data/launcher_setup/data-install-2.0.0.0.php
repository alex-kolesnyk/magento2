<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Launcher
 * @copyright   {copyright}
 * @license     {license_link}
 */

/** @var $installer Mage_Launcher_Model_Resource_Setup */
$installer = $this;
Mage::getModel('Mage_Launcher_Model_Page')
    ->load(1)
    ->setCode('store_launcher')
    ->save();

/** @var $tile Mage_Launcher_Model_Tile */
$tile = Mage::getModel('Mage_Launcher_Model_Tile');
$tile->setCode('business_info');
$tile->setPageId(1);
$tile->setState(0);
$tile->save();

$tile = Mage::getModel('Mage_Launcher_Model_Tile');
$tile->setCode('shipping');
$tile->setPageId(1);
$tile->setState(0);
$tile->save();


$tile = Mage::getModel('Mage_Launcher_Model_Tile');
$tile->setCode('tax');
$tile->setPageId(1);
$tile->setState(0);
$tile->save();
