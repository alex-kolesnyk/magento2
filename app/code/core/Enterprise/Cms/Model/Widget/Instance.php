<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @category    Enterprise
 * @package     Enterprise_Cms
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Cms Widget Instance Model
 *
 * @category    Enterprise
 * @package     Enterprise_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Cms_Model_Widget_Instance extends Mage_Core_Model_Abstract
{
    const SPECIFIC_ENTITIES = 'specific';
    const ALL_ENTITIES      = 'all';

    const DEFAULT_LAYOUT_HANDLE            = 'default';
    const PRODUCT_LAYOUT_HANDLE            = 'catalog_product_view';
    const SINGLE_PRODUCT_LAYOUT_HANLDE     = 'PRODUCT_{{ID}}';
    const PRODUCT_TYPE_LAYOUT_HANDLE       = 'PRODUCT_TYPE_{{TYPE}}';
    const ANCHOR_CATEGORY_LAYOUT_HANDLE    = 'catalog_category_layered';
    const NOTANCHOR_CATEGORY_LAYOUT_HANDLE = 'catalog_category_default';
    const SINGLE_CATEGORY_LAYOUT_HANDLE    = 'CATEGORY_{{ID}}';

    protected $_layoutHandles = array();

    protected $_specificEntitiesLayoutHandles = array();

    /**
     * @var Varien_Simplexml_Element
     */
    protected $_widgetConfigXml = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_init('enterprise_cms/widget_instance');
        $this->_layoutHandles = array(
            'anchor_categories' => self::ANCHOR_CATEGORY_LAYOUT_HANDLE,
            'notanchor_categories' => self::NOTANCHOR_CATEGORY_LAYOUT_HANDLE,
            'all_products' => self::PRODUCT_LAYOUT_HANDLE,
            'all_pages' => self::DEFAULT_LAYOUT_HANDLE
        );
        $this->_specificEntitiesLayoutHandles = array(
            'anchor_categories' => self::SINGLE_CATEGORY_LAYOUT_HANDLE,
            'notanchor_categories' => self::SINGLE_CATEGORY_LAYOUT_HANDLE,
            'all_products' => self::SINGLE_PRODUCT_LAYOUT_HANLDE,
        );
        foreach (Mage_Catalog_Model_Product_Type::getTypes() as $typeId => $type) {
            $this->_layoutHandles[$typeId.'_products'] = str_replace('{{TYPE}}', $typeId, self::PRODUCT_TYPE_LAYOUT_HANDLE) ;
            $this->_specificEntitiesLayoutHandles[$typeId.'_products'] = self::SINGLE_PRODUCT_LAYOUT_HANLDE;
        }
    }

    /**
     * Processing object before save data
     *
     * @return Enterprise_Cms_Model_Widget_Instance
     */
    protected function _beforeSave()
    {
        $pageGroupIds = array();
        $tmpPageGroups = array();
        $pageGroups = $this->getData('page_groups');
        if ($pageGroups) {
            foreach ($pageGroups as $pageGroup) {
                $tmpPageGroup = array();
                if (isset($pageGroup[$pageGroup['page_group']])) {
                    $pageGroupData = $pageGroup[$pageGroup['page_group']];
                    if ($pageGroupData['page_id']) {
                        $pageGroupIds[] = $pageGroupData['page_id'];
                    }
                    if ($pageGroup['page_group'] == 'pages') {
                        $layoutHandle = $pageGroupData['layout_handle'];
                    } else {
                        $layoutHandle = $this->_layoutHandles[$pageGroup['page_group']];
                    }
                    if (!isset($pageGroupData['template'])) {
                        $pageGroupData['template'] = '';
                    }
                    $tmpPageGroup = array(
                        'page_id' => $pageGroupData['page_id'],
                        'group' => $pageGroup['page_group'],
                        'layout_handle' => $layoutHandle,
                        'for' => $pageGroupData['for'],
                        'block_reference' => $pageGroupData['block'],
                        'entities' => '',
                        'layout_handle_updates' => array($layoutHandle),
                        'template' => $pageGroupData['template']?$pageGroupData['template']:'',
                        'position' => $pageGroupData['position']
                    );
                    if ($pageGroupData['for'] == self::SPECIFIC_ENTITIES) {
                        $layoutHandleUpdates = array();
                        foreach (explode(',', $pageGroupData['entities']) as $entity) {
                            $layoutHandleUpdates[] = str_replace('{{ID}}', $entity,
                                $this->_specificEntitiesLayoutHandles[$pageGroup['page_group']]);
                        }
                        $tmpPageGroup['entities'] = $pageGroupData['entities'];
                        $tmpPageGroup['layout_handle_updates'] = $layoutHandleUpdates;
                    }
                    $tmpPageGroups[] = $tmpPageGroup;
                }
            }
        }
        if (is_array($this->getData('store_ids'))) {
            $this->setData('store_ids', implode(',', $this->getData('store_ids')));
        }
        if (is_array($this->getData('widget_parameters'))) {
            $this->setData('widget_parameters', serialize($this->getData('widget_parameters')));
        }
        $this->setData('page_groups', $tmpPageGroups);
        $this->setData('page_group_ids', $pageGroupIds);
        return parent::_beforeSave();
    }

    /**
     * Check if widget instance has required data (other data depends on it)
     *
     * @return boolean
     */
    public function isCompleteToCreate()
    {
        return (bool)($this->getType() && $this->getPackageTheme());
    }

    /**
     * Setter
     * Replace '-' to '/', if was passed from request(GET request)
     *
     * @param string $type
     * @return Enterprise_Cms_Model_Widget_Instance
     */
    public function setType($type)
    {
        if (strpos($type, '-')) {
            $type = str_replace('-', '/', $type);
        }
        $this->setData('type', $type);
        return $this;
    }

    /**
     * Getter
     * Replace '-' to '/', if was set from request(GET request)
     *
     * @return string
     */
    public function getType()
    {
        if (strpos($this->_getData('type'), '-')) {
            $this->setData('type', str_replace('-', '/', $this->_getData('type')));
        }
        return $this->_getData('type');
    }

    /**
     * Setter
     * Replace '_' to '/', if was passed from request(GET request)
     *
     * @param string $packageTheme
     * @return Enterprise_Cms_Model_Widget_Instance
     */
    public function setPackageTheme($packageTheme)
    {
        if (strpos($packageTheme, '_')) {
            $packageTheme = str_replace('_', '/', $packageTheme);
        }
        $this->setData('package_theme', $packageTheme);
        return $this;
    }

    /**
     * Getter.
     * Replace '_' to '/', if was set from request(GET request)
     *
     * @return string
     */
    public function getPackageTheme()
    {
        if (strpos($this->_getData('package_theme'), '_')) {
            $this->setData('package_theme', str_replace('_', '/', $this->_getData('package_theme')));
        }
        return $this->_getData('package_theme');
    }

    /**
     * Getter.
     * If not set return default
     *
     * @return string
     */
    public function getArea()
    {
        if (!$this->_getData('area')) {
            return Mage_Core_Model_Design_Package::DEFAULT_AREA;
        }
        return $this->_getData('area');
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getPackage()
    {
        if (!$this->_getData('package')) {
            $this->_parsePackageTheme();
        }
        return $this->_getData('package');
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getTheme()
    {
        if (!$this->_getData('theme')) {
            $this->_parsePackageTheme();
        }
        return $this->_getData('theme');
    }

    /**
     * Parse packageTheme and set parsed package and theme
     *
     * @return Enterprise_Cms_Model_Widget_Instance
     */
    protected function _parsePackageTheme()
    {
        if ($this->getPackageTheme() && strpos($this->getPackageTheme(), '/')) {
            list($package, $theme) = explode('/', $this->getPackageTheme());
            $this->setData('package', $package);
            $this->setData('theme', $theme);
        }
        return $this;
    }

    /**
     * Getter
     * Explode to array if string setted
     *
     * @return array
     */
    public function getStoreIds()
    {
        if (null !== ($storeIds = $this->getData('store_ids')) && is_string($storeIds)) {
            $this->setData('store_ids', explode(',', $storeIds));
        }
        return $this->getData('store_ids');
    }

    /**
     * Getter
     * Unserialize if serialized string setted
     *
     * @return array
     */
    public function getWidgetParameters()
    {
        if (($widgetParameters = $this->getData('widget_parameters')) && is_string($widgetParameters)) {
            $this->setData('widget_parameters', unserialize($widgetParameters));
        }
        return $this->getData('widget_parameters');
    }

    /**
     * Retrieve option arra of widget types
     *
     * @return array
     */
    public function getWidgetsOptionArray()
    {
        $widgets = array();
        $widgetsArr = Mage::getSingleton('cms/widget')->getWidgetsArray();
        foreach ($widgetsArr as $widget) {
            $widgets[] = array(
                'value' => $widget['type'],
                'label' => $widget['name']
            );
        }
        return $widgets;
    }

    /**
     * Load widget XML config and merge with theme widget config
     *
     * @return Varien_Simplexml_Element
     */
    public function getWidgetConfig()
    {
        if ($this->_widgetConfigXml === null) {
            $this->_widgetConfigXml = Mage::getSingleton('cms/widget')
                ->getXmlElementByType($this->getType());
            $configFile = Mage::getSingleton('core/design_package')->getBaseDir(array(
                '_area'    => $this->getArea(),
                '_package' => $this->getPackage(),
                '_theme'   => $this->getTheme(),
                '_type'    => 'widget'
            )) . DS . 'config.xml';
            if (is_readable($configFile)) {
                $themeConfig = new Varien_Simplexml_Config();
                $themeConfig->loadFile($configFile);
                $this->_widgetConfigXml->extend($themeConfig->getNode('widgets/'.$this->_widgetConfigXml->getName()));
            }
        }
        return $this->_widgetConfigXml;
    }

    /**
     * Retrieve widget availabel templates
     *
     * @return array
     */
    public function getWidgetTemplates()
    {
        $templates = array();
        if ($configTemplates = $this->getWidgetConfig()->parameters->template) {
            if ($configTemplates->values && $configTemplates->values->children()) {
                foreach ($configTemplates->values->children() as $name => $template) {
                    $helper = $template->getAttribute('module') ? $template->getAttribute('module') : 'enterprise_cms';
                    $templates[(string)$name] = array(
                        'value' => (string)$template->value,
                        'label' => Mage::helper($helper)->__((string)$template->label)
                    );
                }
            } elseif ($configTemplates->value) {
                $templates['default'] = array(
                    'value' => (string)$configTemplates->value,
                    'label' => Mage::helper('enterprise_cms')->__('Default Template')
                );
            }
        }
        return $templates;
    }

    /**
     * Retrieve blocks that widget support
     *
     * @return array
     */
    public function getWidgetSupportedBlocks()
    {
        $blocks = array();
        if ($supportedBlocks = $this->getWidgetConfig()->supported_blocks) {
            foreach ($supportedBlocks->children() as $block) {
                $blocks[] = (string)$block->block_name;
            }
        }
        return $blocks;
    }

    /**
     * Retrieve widget templates that supported by given block reference
     *
     * @param string $blockReference
     * @return array
     */
    public function getWidgetSupportedTemplatesByBlock($blockReference)
    {
        $templates = array();
        $widgetTemplates = $this->getWidgetTemplates();
        if (!($supportedBlocks = $this->getWidgetConfig()->supported_blocks)) {
            return $widgetTemplates;
        }
        foreach ($supportedBlocks->children() as $block) {
            if ((string)$block->block_name == $blockReference) {
                if ($block->template && $block->template->children()) {
                    foreach ($block->template->children() as $template) {
                        if (isset($widgetTemplates[(string)$template])) {
                            $templates[] = $widgetTemplates[(string)$template];
                        }
                    }
                } else {
                    $templates[] = $widgetTemplates[(string)$template];
                }
            }
        }
        return $templates;
    }

    /**
     * Generate layout update xml
     *
     * @param string $blockReference
     * @param string $position
     * @return string
     */
    public function generateLayoutUpdateXml($blockReference, $templatePath = null, $position = 'before')
    {
        $templatesDir = Mage::getSingleton('core/design_package')->getBaseDir(array(
            '_area'    => $this->getArea(),
            '_package' => $this->getPackage(),
            '_theme'   => $this->getTheme(),
            '_type'    => 'template'
        ));
        if (!$this->getId() && !$this->isCompleteToCreate()
            || ($templatePath && !is_readable($templatesDir . DS . $templatePath)))
        {
            return '';
        }
        $parameters = $this->getWidgetParameters();
        $xml = '<reference name="'.$blockReference.'">';
        $template = '';
        if (isset($parameters['template'])) {
            unset($parameters['template']);
        }
        if ($templatePath) {
            $template = ' template="'.$templatePath.'"';
        }
        $xml .= '<block type="'.$this->getType().'" name="' . Mage::helper('core')->uniqHash() . '"'.$template.' '.$position.'="-">';
        foreach ($parameters as $name => $value) {
            $xml .= '<action method="setData"><name>'.$name.'</name><value>'.Mage::helper('enterprise_cms')->htmlEscape($value).'</value></action>';
        }
        $xml .= '</block></reference>';
        return $xml;
    }

}
