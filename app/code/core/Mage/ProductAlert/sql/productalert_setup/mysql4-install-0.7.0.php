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
 * @category   Mage
 * @package    Mage_ProductAlert
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * ProductAlert Install
 *
 * @category   Mage
 * @package    Mage_ProductAlert
 * @author     Victor Tihonchuk <victor@varien.com>
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('product_alert_price')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('product_alert_price')}` (
  `alert_price_id` int(10) unsigned NOT NULL auto_increment,
  `customer_id` int(10) unsigned NOT NULL default '0',
  `product_id` int(10) unsigned NOT NULL default '0',
  `price` decimal(12,4) NOT NULL default '0',
  `website_id` smallint(5) unsigned NOT NULL default '0',
  `add_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_send_date` datetime default NULL,
  `send_count` smallint(5) unsigned NOT NULL default '0',
  `status` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`alert_price_id`),
  CONSTRAINT `FK_PRODUCT_ALERT_PRICE_CUSTOMER`
    FOREIGN KEY (`customer_id`)
    REFERENCES `{$installer->getTable('customer_entity')}` (`entity_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `FK_PRODUCT_ALERT_PRICE_PRODUCT`
    FOREIGN KEY (`product_id`)
    REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `FK_PRODUCT_ALERT_PRICE_WEBSITE`
    FOREIGN KEY (`website_id`)
    REFERENCES `{$installer->getTable('core_website')}` (`website_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('product_alert_stock')}`;
CREATE TABLE `{$installer->getTable('product_alert_stock')}` (
  `alert_stock_id` int(10) unsigned NOT NULL auto_increment,
  `customer_id` int(10) unsigned NOT NULL default '0',
  `product_id` int(10) unsigned NOT NULL default '0',
  `website_id` smallint(5) unsigned NOT NULL default '0',
  `add_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `send_date` datetime default NULL,
  `send_count` smallint(5) unsigned NOT NULL default '0',
  `status` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`alert_stock_id`),
  CONSTRAINT `FK_PRODUCT_ALERT_PRICE_CUSTOMER`
    FOREIGN KEY (`customer_id`)
    REFERENCES `{$installer->getTable('customer_entity')}` (`entity_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `FK_PRODUCT_ALERT_PRICE_PRODUCT`
    FOREIGN KEY (`product_id`)
    REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `FK_PRODUCT_ALERT_PRICE_WEBSITE`
    FOREIGN KEY (`website_id`)
    REFERENCES `{$installer->getTable('core_website')}` (`website_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");

$installer->getConnection()->query("
INSERT INTO `{$installer->getTable('core_email_template')}` VALUES
    (NULL, 'Product price alert', 'Hello {{var customerName}},\r\n\r\n{{var alertGrid}}', 2, 'Products price changed alert', NULL, NULL, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `template_text`='Hello {{var customerName}},\r\n\r\n{{var alertGrid}}', `template_type`=2, `template_subject`='Product(s) price alert notification'
");
$templateId = $installer->getConnection()->lastInsertId();
$installer->setConfigData('catalog/productalert/email_price_template', $templateId);

$installer->getConnection()->query("
INSERT INTO `{$installer->getTable('core_email_template')}` VALUES
    (NULL, 'Product stock alert', 'Hello {{var customerName}},\r\n\r\n{{var alertGrid}}', 2, 'Products back in stock alert', NULL, NULL, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `template_text`='Hello {{var customerName}},\r\n\r\n{{var alertGrid}}', `template_type`=2, `template_subject`='Product(s) stock alert notification'
");
$templateId = $installer->getConnection()->lastInsertId();
$installer->setConfigData('catalog/productalert/email_stock_template', $templateId);

$installer->endSetup();