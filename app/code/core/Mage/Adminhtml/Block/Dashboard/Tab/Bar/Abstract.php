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
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml dashboard tab bar abstract
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Ivan Chepurnyi <mitch@varien.com>
 */
 class Mage_Adminhtml_Block_Dashboard_Tab_Bar_Abstract extends Mage_Adminhtml_Block_Widget 
 {
 	protected $_tabs;
 	
	/**
	 * Block data collection
	 *
	 * @var Varien_Data_Collection_Db
	 */
	protected $_collection = null;
 	
 	public function addTab($tabId, $type, array $options) 
 	{
 		$tab = $tab->getTabByType($tabId);
 		$this->_tabs[] = $tab;
 		$this->setChild($tabId, $tab);
 		return $this;
 	}
 	
 	public function getTab($tabId) 
 	{
 		return $this->getChild($tabId);
 	}
 	
 	public function getTabs()
 	{
 		return $this->_tabs;
 	}
 	
 	protected function _prepareCollection()
 	{
 		if($this->getCollection()) {
 			foreach ($this->getTabs() as $tab) {
 				if(!$tab->getCollection()) {
 					$tab->setCollection($this->getCollection());
 				}
 			}
 			$this->getCollection()->load();
 		} 		
 		
 		return $this;
 	}
 	
 	protected function _initTabs()
 	{
 		return $this;
 	}
 	
 	protected function _initChildren()
 	{
 		$this->_initTabs();
 		return parent::_initChildren();
 	}
 	
 	public function getTabByType($type)
 	{
 		$block = '';
 		switch ($type) {
 			case "graph":
 				$block = 'adminhtml/dashboard_tab_graph';
 				break;
 				
 			case "grid":
 			default:
 				$block = 'adminhtml/dashboard_tab_grid';
 				break;
 		}
 		
 		return $this->getLayout()->createBlock($block);
 	}
 	
 	public function getCollection()
	{
		return $this->_collection;
	}
	
	public function setCollection($collection) 
	{
		$this->_collection = $collection;
		return $this;
	}
	
	protected function _beforeToHtml()
	{
		$this->_prepareCollection();
		return parent::_beforeToHtml();
	}
 } // Class Mage_Adminhtml_Block_Dashboard_Tab_Bar_Abstract end