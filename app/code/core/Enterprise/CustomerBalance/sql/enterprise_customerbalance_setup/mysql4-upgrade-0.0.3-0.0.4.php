<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Enterprise
 * @package    Enterprise_CustomerBalance
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$installer->run("DROP TABLE {$installer->getTable('enterprise_customerbalance_history')};");
$installer->run("
CREATE TABLE {$installer->getTable('enterprise_customerbalance_history')} (
 `primary_id` int(10) NOT NULL auto_increment,
 `customer_id` int(10) unsigned NOT NULL,
 `website_id` smallint(5) unsigned NOT NULL,
 `action` tinyint(1) unsigned NOT NULL,
 `date` datetime NOT NULL,
 `admin_user` varchar(255) NOT NULL,
 `delta` decimal(12,4) NOT NULL,
 `balance` decimal(12,4) NOT NULL,
 `order_increment_id` varchar(30) default NULL,
 `notified` tinyint(1) unsigned NOT NULL default '0',
 PRIMARY KEY  (`primary_id`),
 KEY `FK_CUSTOMERBALANCE_HISTORY_CUSTOMER_ENTITY` (`customer_id`),
 CONSTRAINT `FK_CUSTOMERBALANCE_HISTORY_CUSTOMER_ENTITY` FOREIGN KEY (`customer_id`) REFERENCES {$installer->getTable('customer_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
");

$installer->endSetup();