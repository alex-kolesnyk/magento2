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
 * Store grid column filter
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Victor Tihonchuk <victor@varien.com>
 */
class Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Store extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Abstract
{

    public function getHtml()
    {
        $storeModel = Mage::getSingleton('adminhtml/system_store');
        /* @var $storeModel Mage_Adminhtml_Model_System_Store */
        $websiteCollection = $storeModel->getWebsiteCollection();
        $groupCollection = $storeModel->getGroupCollection();
        $storeCollection = $storeModel->getStoreCollection();

        $allShow = $this->getColumn()->getStoreAll();

        $html  = '<select name="' . ($this->getColumn()->getName() ? $this->getColumn()->getName() : $this->getColumn()->getId()) . '" ' . $this->getColumn()->getValidateClass() . '">';
        $value = $this->getColumn()->getValue();
        $html .= '<option value=""' . ((!$value || $value == 0 && !$allShow) ? ' selected="true"' : '') . '></option>';
        if ($allShow) {
            $html .= '<option value="0"' . ($value == 0 ? ' selected="true"' : '') . '>' . Mage::helper('adminhtml')->__('All Store Views') . '</option>';
        }
        foreach ($websiteCollection as $website) {
            $websiteShow = false;
            foreach ($groupCollection as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                $groupShow = false;
                foreach ($storeCollection as $store) {
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $websiteShow = true;
                        $html .= '<optgroup label="' . $website->getName() . '"></optgroup>';
                    }
                    if (!$groupShow) {
                        $groupShow = true;
                        $html .= '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;' . $group->getName() . '">';
                    }
                    $value = $this->getValue();
                    $html .= '<option value="' . $store->getId() . '"' . ($value == $store->getId() ? ' selected="true"' : '') . '>&nbsp;&nbsp;&nbsp;&nbsp;' . $store->getName() . '</option>';
                }
                if ($groupShow) {
                    $html .= '</optgroup>';
                }
            }
        }
        $html .= '</select>';
        return $html;
    }

    public function getCondition()
    {
        if (is_null($this->getValue())) {
            return null;
        }
        return array('eq' => $this->getValue());
    }

}
