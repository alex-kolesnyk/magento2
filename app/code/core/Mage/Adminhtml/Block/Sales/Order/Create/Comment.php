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
 * @package    Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Create order comment form
 *
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Create_Comment extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{
    protected $_form;
    
    public function __construct() 
    {
        parent::__construct();
        $this->setTemplate('sales/order/create/comment.phtml');
    }
    
    public function getHeaderCssClass()
    {
        return 'head-comment';
    }
    
    public function getHeaderText()
    {
        return $this->__('Order Comment');
    }
}