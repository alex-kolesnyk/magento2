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
 * @package    Mage_Bundle
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml product edit tabs
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Bundle_Block_Adminhtml_Catalog_Product_Edit_Tabs extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
{
    protected $_attributeTabBlock = 'bundle/adminhtml_catalog_product_edit_tab_attributes';

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->addTab('bundle_items', array(
            'label'     => Mage::helper('catalog')->__('Bundle Items'),
            'content'   => $this->getLayout()->createBlock('bundle/adminhtml_catalog_product_edit_tab_bundle', 'admin.product.bundle.items')
                ->setProductId($this->getRequest()->getParam('id'))
                ->toHtml(),
        ));
    }
}