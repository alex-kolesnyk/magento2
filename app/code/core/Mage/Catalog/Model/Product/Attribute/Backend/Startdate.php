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
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * Speical Start Date attribute backend
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Lindy Kyaw <lindy@varien.com>
 */

class Mage_Catalog_Model_Product_Attribute_Backend_Startdate extends Mage_Eav_Model_Entity_Attribute_Backend_Datetime
{
    public function beforeSave($object)
    {
        $attributeName = $this->getAttribute()->getName();

        $startDate = $object->getData($attributeName);

        if ($startDate=='' && $object->getSpecialPrice()) {
            //$startDate = Mage::app()->getLocale()->date();
            $startDate = Mage::getModel('core/date')->gmtDate();
        }

        $object->setData($attributeName, $startDate);

        parent::beforeSave($object);

        return $this;
    }

}
