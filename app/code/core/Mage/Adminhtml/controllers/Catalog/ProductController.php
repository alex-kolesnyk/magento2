<?php
/**
 * Catalog product controller
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout('baseframe');
        $this->_setActiveMenu('catalog/products');
        
        /**
         * Append customers block to content
         */
        $this->_addContent(
            $this->getLayout()->createBlock('adminhtml/catalog_product')
        );
        
        $this->renderLayout();
    }
    
    public function gridAction()
    {
        
    }
    
    public function newAction()
    {
        $this->_forward('edit');
    }
    
    public function editAction()
    {
        $this->loadLayout('baseframe');
        $this->_setActiveMenu('catalog/products');
        $this->getLayout()->getBlock('root')->setCanLoadExtJs(true);
        
        $productId  = (int) $this->getRequest()->getParam('id');
        $product    = Mage::getModel('catalog/product');
        
        if ($productId) {
            $product->load($productId);
            
            if($this->getRequest()->getParam('store')) {
            	$product->getRelatedProducts()
	            	->joinField('store_id', 
	                'catalog/product_store', 
	                'store_id', 
	                'product_id=entity_id', 
	                '{{table}}.store_id='.(int) $this->getRequest()->getParam('store', 0));
            		
            }
            $product->getRelatedProducts()->load();
            
            if($this->getRequest()->getParam('store')) {
            	$product->getUpSellProducts()
            		->joinField('store_id', 
		                'catalog/product_store', 
		                'store_id', 
		                'product_id=entity_id', 
		                '{{table}}.store_id='.(int) $this->getRequest()->getParam('store', 0));
            		
            }
            $product->getUpSellProducts()->load();
            
        	if($this->getRequest()->getParam('store')) {
            	$product->getCrossSellProducts()
            		->joinField('store_id', 
		                'catalog/product_store', 
		                'store_id', 
		                'product_id=entity_id', 
		                '{{table}}.store_id='.(int) $this->getRequest()->getParam('store', 0));
            }
            $product->getCrossSellProducts()->load();
        }
        
        Mage::register('product', $product);
        
        $this->_addContent($this->getLayout()->createBlock('adminhtml/catalog_product_edit'));
        $this->_addLeft($this->getLayout()->createBlock('adminhtml/catalog_product_edit_tabs'));
        
        $this->renderLayout();
    }
    
    public function relatedAction()
    {
        $this->_initProduct('related');
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_related')->toHtml()
        );       
    }
    
    public function upsellAction()
    {
        $this->_initProduct('up_sell');
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_upsell')->toHtml()
        );       
    }
    
    public function crosssellAction()
    {
        $this->_initProduct('cross_sell');
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_crosssell')->toHtml()
        );       
    }
    
    protected function _initProduct($type)
    {
    	$productId  = (int) $this->getRequest()->getParam('id');
        $product    = Mage::getModel('catalog/product');
        
        if ($productId) {
            $product->load($productId);
            if($this->getRequest()->getParam('store')) {
            	$product->getLinkedProducts($type)
            		->joinField('store_id', 
		                'catalog/product_store', 
		                'store_id', 
		                'product_id=entity_id', 
		                '{{table}}.store_id='.(int) $this->getRequest()->getParam('store', 0));
            }
            $product->getLinkedProducts($type)->load();
        }

        
               
        Mage::register('product', $product);
    }
    
    public function saveAction()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        if ($data = $this->getRequest()->getPost()) {
            $relatedProducts = array();
            $upSellProducts = array();
            $crossSellProducts = array();
                
            if($this->getRequest()->getPost('_related_products')) {
            	$relatedProducts = $this->_decodeInput($this->getRequest()->getPost('_related_products'));
            } 
            
            if($this->getRequest()->getPost('_up_sell_products')) {
            	$upSellProducts = $this->_decodeInput($this->getRequest()->getPost('_up_sell_products'));
            } 
            
            if($this->getRequest()->getPost('_cross_sell_products')) {
            	$crossSellProducts = $this->_decodeInput($this->getRequest()->getPost('_cross_sell_products'));
            } 
           	        	
        	$product = Mage::getModel('catalog/product')
                ->setData($data['product'])
                ->setId($this->getRequest()->getParam('id'))
                ->setStoreId($storeId)
                ->setAttributeSetId(9)
                ->setRelatedProducts(array_keys($relatedProducts), array_values($relatedProducts))
                ->setUpSellProducts(array_keys($upSellProducts), array_values($upSellProducts))
                ->setCrossSellProducts(array_keys($crossSellProducts), array_values($crossSellProducts));
                        
                            
            try {
                $product->save();
                Mage::getSingleton('adminhtml/session')->addSuccess('Product saved');
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')
                    ->addError($e->getMessage())
                    ->setCategoryData($data);
                $this->getResponse()->setRedirect(Mage::getUrl('*/*/edit', array('id'=>$product->getId(), 'store'=>$storeId)));
                return;
            }
        }

        $this->getResponse()->setRedirect(Mage::getUrl('*/*/', array('store'=>$storeId)));
        
        
	    
        
    }
    
    /**
     * Decode strings for linked products
     *
     * @param 	string $encoded
     * @return 	array
     */
    protected function _decodeInput($encoded)
    {
    	parse_str($encoded, $data);
        foreach($data as $key=>$value) {
        	parse_str(base64_decode($value), $data[$key]);
        }
        
        return $data;
    }
        
    
    public function deleteAction()
    {
        
    }
    
    public function exportCsvAction()
    {
        
    }
    
    public function exportXmlAction()
    {
        
    }
}
