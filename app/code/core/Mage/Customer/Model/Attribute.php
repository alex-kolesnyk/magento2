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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Customer
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer attribute model
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Customer_Model_Attribute extends Mage_Eav_Model_Entity_Attribute
{
    const MODULE_NAME = 'Mage_Customer';

    protected $_eventPrefix = 'customer_entity_attribute';
    protected $_eventObject = 'attribute';

    /**
     * Active Website instance
     *
     * @var Mage_Core_Model_Website
     */
    protected $_website;

    /**
     * Set active website instance
     *
     * @param Mage_Core_Model_Website|int $website
     * @return Mage_Customer_Model_Attribute
     */
    public function setWebsite($website)
    {
        $this->_website = Mage::app()->getWebsite($website);
        return $this;
    }

    /**
     * Return active website instance
     *
     * @return Mage_Core_Model_Website
     */
    public function getWebsite()
    {
        if (is_null($this->_website)) {
            $this->_website = Mage::app()->getWebsite();
        }

        return $this->_website;
    }

    protected function _construct()
    {
        $this->_init('customer/attribute');
    }

    /**
     * Processing object after save data
     *
     * @return Mage_Customer_Model_Attribute
     */
    protected function _afterSave()
    {
        Mage::getSingleton('eav/config')->clear();
        return parent::_afterSave();
    }

    /**
     * Return forms in which the attribute
     *
     * @return array
     */
    public function getUsedInForms()
    {
        $forms = $this->getData('used_in_forms');
        if (is_null($forms)) {
            $forms = $this->_getResource()->getUsedInForms($this);
            $this->setData('used_in_forms', $forms);
        }
        return $forms;
    }

    /**
     * Return validate rules
     *
     * @return array
     */
    public function getValidateRules()
    {
        $rules = $this->getData('validate_rules');
        if (!empty($rules)) {
            return unserialize($rules);
        }
        return array();
    }

    public function setValidateRules($rules)
    {
        if (empty($rules)) {
            $rules = null;
        } else if (is_array($rules)) {
            $rules = serialize($rules);
        }
        $this->setData('validate_rules', $rules);
    }

    /**
     * Return scope value by key
     *
     * @param string $key
     * @return mixed
     */
    protected function _getScopeValue($key)
    {
        $scopeKey = sprintf('scope_%s', $key);
        if ($this->hasData($scopeKey)) {
            return $this->getData($scopeKey);
        }
        return $this->getData($key);
    }

    /**
     * Return is attribute value required
     *
     * @return int
     */
    public function getIsRequired()
    {
        return $this->_getScopeValue('is_required');
    }

    /**
     * Return is visible attribute flag
     *
     * @return int
     */
    public function getIsVisible()
    {
        return $this->_getScopeValue('is_visible');
    }

    /**
     * Return default value for attribute
     *
     * @return int
     */
    public function getDefaultValue()
    {
        return $this->_getScopeValue('default_value');
    }

    /**
     * Return count of lines for multiply line attribute
     *
     * @return int
     */
    public function getMultilineCount()
    {
        return $this->_getScopeValue('multiline_count');
    }
}
