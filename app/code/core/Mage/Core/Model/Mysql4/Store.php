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


class Mage_Core_Model_Mysql4_Store extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('core/store', 'store_id');
    }
    
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
    	parent::_afterSave($object);
    	$this->updateDatasharing();
    }
    
    public function updateDatasharing()
    {
    	$this->getConnection('write')->delete($this->getTable('config_data'), "path like 'advanced/datashare/%'");
    	
    	$websites = Mage::getResourceModel('core/website_collection')->setLoadDefault(true)->load();
    	$stores = Mage::getResourceModel('core/store_collection')->setLoadDefault(true)->load();
    	$fields = Mage::getResourceModel('core/config_field_collection')
    		->addFieldToFilter('path', array('like'=>'advanced/datashare/%'))
    		->load();
    	$data = Mage::getModel('core/config_data')
    		->setScope('websites');
    	
    	$allStoreIds = array();
    	foreach ($stores as $s) {
    		$w = $websites->getItemById($s->getWebsiteId());
    		if (!$w) {
    			continue;
    		}
    		$stores = $w->getStores();
    		if (empty($stores)) {
    			$stores = array();
    		}
    		$stores[] = $s->getId();
    		$w->setStores($stores);
    		$allStoreIds[] = $s->getId();
    	}
    	$websites->getItemById(0)->setStores($allStoreIds);
    	
    	foreach ($websites as $w) {
    		if (!$w->getStores()) {
    			continue;
    		}
    		$data->unsConfigId()
    			->setScopeId($w->getId())
    			->setValue(join(',',$w->getStores()));
    		foreach ($fields as $f) {
    			$data->setPath($f->getPath());
    			$data->save();
    		}
    	}
    	return $this;
    }
}