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
 * @package    Mage_Eav
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Eav_Model_Entity_Type extends Mage_Core_Model_Abstract
{
    protected $_attributes;
    protected $_attributesBySet = array();
    protected $_sets;
    
    protected function _construct()
    {
        $this->_init('eav/entity_type');
    }

    public function loadByCode($code)
    {
        $this->getResource()->loadByCode($this, $code);
        return $this;
    }
    
    public function getAttributeCollection($setId = null)
    {
        if (is_null($setId)) {
            if (is_null($this->_attributes)) {
                $this->_attributes = Mage::getModel('eav/entity_attribute')->getResourceCollection()
                    ->setEntityTypeFilter($this->getId());
            }
            $collection = $this->_attributes;
        }
        else {
            if (!isset($this->_attributesBySet[$setId])) {
                $this->_attributesBySet[$setId] = Mage::getModel('eav/entity_attribute')->getResourceCollection()
                    ->setEntityTypeFilter($this->getId());
            }
            $collection = $this->_attributesBySet[$setId];
        }
        return $collection;
    }
    
    public function getAttributeSetCollection()
    {
        if (empty($this->_sets)) {
            $this->_sets = Mage::getModel('eav/entity_attribute_set')->getResourceCollection()
                ->setEntityTypeFilter($this->getId());
        }
        return $this->_sets;
    }
    
    public function fetchNewIncrementId($storeId=null)
    {
        if (!$this->getIncrementModel()) {
            return false;
        }
        
        if (!$this->getIncrementPerStore()) {
            $storeId = 0;
        } elseif (!$storeId) {
            throw Mage::exception('Mage_Eav', 'Valid store_id is expected!');
        }
        
        $entityStoreConfig = Mage::getModel('eav/entity_store')
            ->loadByEntityStore($this->getId(), $storeId);
            
        if (!$entityStoreConfig->getId()) {
            $entityStoreConfig
                ->setEntityTypeId($this->getId())
                ->setStoreId($storeId)
                ->setIncrementPrefix($storeId)
                ->save();
        }
        
        $incrementInstance = Mage::getModel($this->getIncrementModel())
            ->setPrefix($entityStoreConfig->getIncrementPrefix())
            ->setPadLength($entityStoreConfig->getIncrementPadLength())
            ->setPadChar($entityStoreConfig->getIncrementPadChar())
            ->setLastId($entityStoreConfig->getIncrementLastId())
        ;
        
        // do read lock on eav/entity_store to solve potential timing issues
        // (most probably already done by beginTransaction of entity save)
        
        $incrementId = $incrementInstance->getNextId();
        
        $entityStoreConfig->setIncrementLastId($incrementId);

        $entityStoreConfig->save();
        
        return $incrementId;
    }
}