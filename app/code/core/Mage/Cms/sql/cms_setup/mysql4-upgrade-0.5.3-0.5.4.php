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
 * @package    Mage_Cms
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


$this->setConfigData('web/default/front', 'cms');

$this->run(<<<EOT

alter table `cms_page` add unique `identifier` (`identifier`);

replace into `cms_page`(`page_id`,`title`,`root_template`,`meta_keywords`,`meta_description`,`identifier`,`content`,`creation_time`,`update_time`,`is_active`,`store_id`,`sort_order`) values (1,'404 Not Found 1','three_column','Page keywords','Page description','no-route','<h1 class=\"page-heading\">404 Error</h1>\r\n<p>\r\nPage not found.<br />\r\n<em>by NoRoute Action :-)</em>\r\n</p>\r\n','2007-06-20 18:38:32','2007-08-23 10:03:38',1,0,0);

replace  into `cms_page`(`page_id`,`title`,`root_template`,`meta_keywords`,`meta_description`,`identifier`,`content`,`creation_time`,`update_time`,`is_active`,`store_id`,`sort_order`) values (2,'Home page','right_column','','','home','<div class=\"col-left side-col\">\r\n    <p class=\"home-callout\"><img src=\"{{skin url=\'images/ph_callout_left_top.gif\'}}\" alt=\"\"/></p>\r\n    <p class=\"home-callout\"><img src=\"{{skin url=\'images/ph_callout_left_rebel.jpg\'}}\" alt=\"\"/></p>\r\n    {{block type=\"tag/popular\" template=\"tag/popular.phtml\"}}\r\n</div>\r\n\r\n<div class=\"home-spot\">\r\n    <p class=\"home-callout\"><img src=\"{{skin url=\'images/home_main_callout.jpg\'}}\" alt=\"\"/></p>\r\n    <p class=\"home-callout\"><img src=\"{{skin url=\'images/free_shipping_callout.jpg\'}}\" alt=\"\"/></p>\r\n    <div class=\"box best-selling\">\r\n        <h3>Best Selling Products</h3>\r\n        <table cellspacing=\"0\">\r\n            <tr class=\"odd\">\r\n                <td><a href=\"#\"><img src=\"{{skin url=\'images/best_selling_img04.jpg\'}}\" width=\"95\" alt=\"\" class=\"product-img\"></a>\r\n                  <div class=\"product-description\">\r\n                    <p><a href=\"#\" class=\"product-name\">Zynicon Cinema Display 19-inch LCD monitor</a></p>\r\n                    <p>See all <a href=\"#\">Computers</a></p>\r\n                   </div></td>\r\n                <td><a href=\"#\"><img src=\"{{skin url=\'images/best_selling_img06.jpg\'}}\" width=\"95\" alt=\"\" class=\"product-img\"></a>\r\n                  <div class=\"product-description\">\r\n                    <p><a href=\"#\" class=\"product-name\">Sandra Mashiso Red Business shoes</a></p>\r\n                    <p>See all <a href=\"#\">Shoes</a></p>\r\n                   </div></td>\r\n            </tr>\r\n            <tr class=\"even\">\r\n                <td><a href=\"#\"><img src=\"{{skin url=\'images/best_selling_img03.jpg\'}}\" width=\"95\" alt=\"\" class=\"product-img\"></a>\r\n                  <div class=\"product-description\">\r\n                    <p><a href=\"#\" class=\"product-name\">Panasoic HDC-SD1 High Definition Camcorder</a></p>\r\n                    <p>See all <a href=\"#\">Shoes</a></p>\r\n                   </div></td>\r\n                <td><a href=\"#\"><img src=\"{{skin url=\'images/best_selling_img01.jpg\'}}\" width=\"95\" alt=\"\" class=\"product-img\"></a>\r\n                  <div class=\"product-description\">\r\n                    <p><a href=\"#\" class=\"product-name\">Zynicon Clear Blue 24-inch LCD monitor</a></p>\r\n                    <p>See all <a href=\"#\">Women\'s Shoes</a></p>\r\n                  </div></td>\r\n            </tr>\r\n            <tr class=\"odd\">\r\n                <td><a href=\"#\"><img src=\"{{skin url=\'images/best_selling_img05.jpg\'}}\" width=\"95\" alt=\"\" class=\"product-img\"></a>\r\n                  <div class=\"product-description\">\r\n                    <p><a href=\"#\" class=\"product-name\">Kamper state-of-art Extra Comfort runners</a></p>\r\n                    <p>See all <a href=\"#\">Shoes</a></p>\r\n                   </div></td>\r\n                <td><a href=\"#\"><img src=\"{{skin url=\'images/best_selling_img02.jpg\'}}\" width=\"95\" alt=\"\" class=\"product-img\"></a>\r\n                  <div class=\"product-description\">\r\n                    <p><a href=\"#\" class=\"product-name\">Clean boot cut women\'s business pant</a></p>\r\n                    <p>See all <a href=\"#\">Women\'s Shoes</a></p>\r\n                   </div></td>\r\n             </tr>\r\n        </table>\r\n       </div>\r\n</div>\r\n','2007-08-23 10:03:25','2007-08-23 10:32:42',1,0,0);


EOT
);