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
 * Adminhtml dashboard order amounts diagram
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author	   Dmytro Vasylenko <dmitriy.vasilenko@varien.com>
 */

class Mage_Adminhtml_Block_Dashboard_Tab_Amounts extends Mage_Adminhtml_Block_Dashboard_Graph
{
    public function __construct()
    {
        $this->setHtmlId('amounts');
        parent::__construct();
    }

    protected function _prepareData()
    {
        $this->setDataHelperName('adminhtml/dashboard_order');
        $this->getDataHelper()->setParam('store', $this->getRequest()->getParam('store'));
        $this->getDataHelper()->setParam('website', $this->getRequest()->getParam('website'));
        $this->getDataHelper()->setParam('group', $this->getRequest()->getParam('group'));
        $this->getDataHelper()->setParam(
            'period',
            $this->getRequest()->getParam('period')?$this->getRequest()->getParam('period'):'24h'
            );

        $this->setDataRows('revenue');
        $this->_axisMaps = array(
            'x' => 'range',
            'y' => 'revenue');

        parent::_prepareData();
    }
}

