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
 * @package    Mage_Core
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Core store block
 *
 * @category   Mage
 * @package    Mage_Core
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Core_Block_Store extends Mage_Core_Block_Template 
{
    public function __construct() 
    {
        $this->setTemplate('core/store.phtml');

        $website = Mage::getSingleton('core/website');
        $storeCodes = $website->getStoreCodes();
        
        $arrLanguages = array();
        foreach ($storeCodes as $storeCode) {
            if ($storeCode!='admin') {
                $store = Mage::getModel('core/store')->setCode($storeCode);
                $language = $store->getConfig('general/local/language');
            	if (Mage::getSingleton('core/store')->getLanguageCode() != $language) {
            	    $arrLanguages[$language] = $store->getUrl(array());
            	}
            	else {
            	    $arrLanguages[$language] = false;
            	}
            }
        }
        $this->assign('languages', $arrLanguages);
    }
}
