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


class Mage_Catalog_SitemapController extends Mage_Core_Controller_Front_Action {
	
    public function preDispatch(){
        parent::preDispatch();
        if(!Mage::getStoreConfig('catalog/seo/site_map')){
    		  $this->_redirect('noroute');
    		  $this->setFlag('',self::FLAG_NO_DISPATCH,true);
    	}
    	return $this;    
        
    }
    public function categoryAction()
    {    	
    	$this->loadLayout();   
        $this->getLayout()->getBlock('catalog_sitemap_container')->setActiveTab('category');       	  	
    	$this->renderLayout();    	
    }
    
     public function productAction()
    {
    	$this->loadLayout();  	    	  	
	    $this->renderLayout();    	
    }    
   
}

