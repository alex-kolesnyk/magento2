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
 * Tag report admin controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Dmytro Vasylenko <dimav@varien.com>
 */
class Mage_Adminhtml_Report_TagController extends Mage_Adminhtml_Controller_Action
{
    public function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(__('Reports'), __('Reports'))
            ->_addBreadcrumb(__('Tag'), __('Tag'));
        return $this;
    }

    public function customerAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/tag/customer')
            ->_addBreadcrumb(__('Customers Report'), __('Customers Report'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_tag_customer'))
            ->renderLayout();
    }

    /**
     * Export customer's tags report to CSV format
     */
    public function exportCustomerCsvAction()
    {
        $fileName   = 'tag_customer.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/report_tag_customer_grid')
            ->getCsv();
        
        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Export customer's tags report to XML format
     */
    public function exportCustomerXmlAction()
    {
        $fileName   = 'tag_customer.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/report_tag_customer_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }
    
    public function productAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/tag/product')
            ->_addBreadcrumb(__('Poducts Report'), __('Products Report'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_tag_product'))
            ->renderLayout();
    }

    /**
     * Export product's tags report to CSV format
     */
    public function exportProductCsvAction()
    {
        $fileName   = 'tag_product.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/report_tag_product_grid')
            ->getCsv();
        
        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Export product's tags report to XML format
     */
    public function exportProductXmlAction()
    {
        $fileName   = 'tag_product.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/report_tag_product_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }
    
    public function productAllAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/tag/product/all')
            ->_addBreadcrumb(__('Poducts Report (Total)'), __('Products Report (Total)'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_tag_product_all'))
            ->renderLayout();
    }

    /**
     * Export product's total tags report to CSV format
     */
    public function exportProductAllCsvAction()
    {
        $fileName   = 'tag_product_total.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/report_tag_product_all_grid')
            ->getCsv();
        
        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Export product's total tags report to XML format
     */
    public function exportProductAllXmlAction()
    {
        $fileName   = 'tag_product_total.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/report_tag_product_all_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }
    
    public function popularAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/tag/popular')
            ->_addBreadcrumb(__('Popular Tags'), __('Popular Tags'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_tag_popular'))
            ->renderLayout();
    }
    
    /**
     * Export popular tags report to CSV format
     */
    public function exportPopularCsvAction()
    {
        $fileName   = 'tag_popular.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/report_tag_popular_grid')
            ->getCsv();
        
        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Export popular tags report to XML format
     */
    public function exportPopularXmlAction()
    {
        $fileName   = 'tag_popular.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/report_tag_popular_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function customerDetailAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/tag/customerDetail')
            ->_addBreadcrumb(__('Customers Report'), __('Customers Report'))
            ->_addBreadcrumb(__('Customer Tags'), __('Customer Tags'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_tag_customer_detail'))
            ->renderLayout();
    }
    
    /**
     * Export customer's tags detail report to CSV format
     */
    public function exportCustomerDetailCsvAction()
    {
        $fileName   = 'tag_customer_detail.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/report_tag_customer_detail_grid')
            ->getCsv();
        
        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Export customer's tags detail report to XML format
     */
    public function exportCustomerDetailXmlAction()
    {
        $fileName   = 'tag_customer_detail.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/report_tag_customer_detail_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function productDetailAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/tag/productDetail')
            ->_addBreadcrumb(__('Products Report'), __('Products Report'))
            ->_addBreadcrumb(__('Product Tags'), __('Product Tags'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_tag_product_detail'))
            ->renderLayout();
    }

    /**
     * Export product's tags detail report to CSV format
     */
    public function exportProductDetailCsvAction()
    {
        $fileName   = 'tag_product_detail.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/report_tag_product_detail_grid')
            ->getCsv();
        
        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Export product's tags detail report to XML format
     */
    public function exportProductDetailXmlAction()
    {
        $fileName   = 'tag_product_detail.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/report_tag_product_detail_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }
    
    public function tagDetailAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/tag/tagDetail')
            ->_addBreadcrumb(__('Popular Tags'), __('Popular Tags'))
            ->_addBreadcrumb(__('Tag Detail'), __('Tag Detail'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_tag_popular_detail'))
            ->renderLayout();
    }
    
    /**
     * Export tag detail report to CSV format
     */
    public function exportTagDetailCsvAction()
    {
        $fileName   = 'tag_detail.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/report_tag_popular_detail_grid')
            ->getCsv();
        
        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Export tag detail report to XML format
     */
    public function exportTagDetailXmlAction()
    {
        $fileName   = 'tag_detail.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/report_tag_popular_detail_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _isAllowed()
    {
	    switch ($this->getRequest()->getActionName()) {
            case 'customer':
                return Mage::getSingleton('admin/session')->isAllowed('report/tags/customer');
                break;
            case 'product':
                return Mage::getSingleton('admin/session')->isAllowed('report/tags/product');
                break;
            case 'productAll':
                return Mage::getSingleton('admin/session')->isAllowed('report/tags/product_total');
                break;
            case 'popular':
                return Mage::getSingleton('admin/session')->isAllowed('report/tags/popular');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('report/tags');
                break;
        }
    }
    
    protected function _sendUploadResponse($fileName, $content)
    {
        header('HTTP/1.1 200 OK');
        header('Content-Disposition: attachment; filename='.$fileName);
        header('Last-Modified: '.date('r'));
        header("Accept-Ranges: bytes");
        header("Content-Length: ".sizeof($content));
        header("Content-type: application/octet-stream");
        echo $content;
    }    
}