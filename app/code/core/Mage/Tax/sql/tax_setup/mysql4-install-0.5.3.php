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
 * @package    Mage_Tax
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


$this->startSetup();

$this->run(<<<EOT


DROP TABLE IF EXISTS `tax_class`;

CREATE TABLE `tax_class` (
  `class_id` smallint(6) NOT NULL auto_increment,
  `class_name` varchar(255) NOT NULL default '',
  `class_type` enum('CUSTOMER','PRODUCT') NOT NULL default 'CUSTOMER',
  PRIMARY KEY  (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tax_class` */

/*Table structure for table `tax_class_group` */

DROP TABLE IF EXISTS `tax_class_group`;

CREATE TABLE `tax_class_group` (
  `group_id` smallint(6) NOT NULL auto_increment,
  `class_parent_id` smallint(6) NOT NULL default '0',
  `class_group_id` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`group_id`),
  KEY `class_parent_id` (`class_parent_id`),
  CONSTRAINT `FK_TAX_CLASS_GROUP_TAX_CLASS` FOREIGN KEY (`class_parent_id`) REFERENCES `tax_class` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tax_class_group` */

/*Table structure for table `tax_rate` */

DROP TABLE IF EXISTS `tax_rate`;

CREATE TABLE `tax_rate` (
  `tax_rate_id` tinyint(4) NOT NULL auto_increment,
  `tax_county_id` smallint(6) default NULL,
  `tax_region_id` mediumint(9) unsigned default NULL,
  `tax_postcode` varchar(12) default NULL,
  PRIMARY KEY  (`tax_rate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Base tax rates';

/*Data for the table `tax_rate` */

/*Table structure for table `tax_rate_data` */

DROP TABLE IF EXISTS `tax_rate_data`;

CREATE TABLE `tax_rate_data` (
  `tax_rate_data_id` tinyint(4) NOT NULL auto_increment,
  `tax_rate_id` tinyint(4) NOT NULL default '0',
  `rate_value` decimal(12,4) NOT NULL default '0.0000',
  `rate_type_id` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`tax_rate_data_id`),
  KEY `rate_id` (`tax_rate_id`),
  KEY `rate_type_id` (`rate_type_id`),
  CONSTRAINT `FK_TAX_RATE_DATE_TAX_RATE_TYPE` FOREIGN KEY (`rate_type_id`) REFERENCES `tax_rate_type` (`type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_TAX_RATE_DATA_TAX_RATE` FOREIGN KEY (`tax_rate_id`) REFERENCES `tax_rate` (`tax_rate_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tax_rate_data` */

/*Table structure for table `tax_rate_type` */

DROP TABLE IF EXISTS `tax_rate_type`;

CREATE TABLE `tax_rate_type` (
  `type_id` tinyint(4) NOT NULL auto_increment,
  `type_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tax_rate_type` */

insert  into `tax_rate_type`(`type_id`,`type_name`) values (1,'Rate 1'),(2,'Rate 2'),(3,'Rate 3'),(4,'Rate 4'),(5,'Rate 5');

/*Table structure for table `tax_rule` */

DROP TABLE IF EXISTS `tax_rule`;

CREATE TABLE `tax_rule` (
  `tax_rule_id` tinyint(4) NOT NULL auto_increment,
  `tax_customer_class_id` smallint(6) NOT NULL default '0',
  `tax_product_class_id` smallint(6) NOT NULL default '0',
  `tax_rate_type_id` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`tax_rule_id`),
  KEY `tax_customer_class_id` (`tax_customer_class_id`,`tax_product_class_id`),
  KEY `tax_customer_class_id_2` (`tax_customer_class_id`),
  KEY `tax_product_class_id` (`tax_product_class_id`),
  KEY `tax_rate_id` (`tax_rate_type_id`),
  CONSTRAINT `FK_TAX_RULE_TAX_CLASS_CUSTOMER` FOREIGN KEY (`tax_customer_class_id`) REFERENCES `tax_class` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_TAX_RULE_TAX_CLASS_PRODUCT` FOREIGN KEY (`tax_product_class_id`) REFERENCES `tax_class` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


EOT
);

$this->endSetup();
