<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Adminhtml catalog super product configurable tab
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Magento_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config
    extends Magento_Backend_Block_Widget
    implements Magento_Backend_Block_Widget_Tab_Interface
{
    protected $_template = 'catalog/product/edit/super/config.phtml';

    /**
     * Catalog data
     *
     * @var Magento_Catalog_Helper_Data
     */
    protected $_catalogData = null;

    /**
     * @var Magento_Core_Model_App
     */
    protected $_app;

    /**
     * @var Magento_Core_Model_LocaleInterface
     */
    protected $_locale;

    /**
     * Core registry
     *
     * @var Magento_Core_Model_Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Magento_Catalog_Helper_Data $catalogData
     * @param Magento_Core_Model_App $app
     * @param Magento_Core_Model_LocaleInterface $locale
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Backend_Block_Template_Context $context
     * @param Magento_Core_Model_Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Magento_Catalog_Helper_Data $catalogData,
        Magento_Core_Model_App $app,
        Magento_Core_Model_LocaleInterface $locale,
        Magento_Core_Helper_Data $coreData,
        Magento_Backend_Block_Template_Context $context,
        Magento_Core_Model_Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_catalogData = $catalogData;
        $this->_app = $app;
        $this->_locale = $locale;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Initialize block
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setProductId($this->getRequest()->getParam('id'));

        $this->setId('config_super_product');
        $this->setCanEditPrice(true);
        $this->setCanReadPrice(true);
    }

    /**
     * Retrieve Tab class (for loading)
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax';
    }

    /**
     * Check block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return (bool) $this->getProduct()->getCompositeReadonly();
    }

    /**
     * Check whether attributes of configurable products can be editable
     *
     * @return boolean
     */
    public function isAttributesConfigurationReadonly()
    {
        return (bool)$this->getProduct()->getAttributesConfigurationReadonly();
    }

    /**
     * Get configurable product type
     *
     * @return Magento_Catalog_Model_Product_Type_Configurable
     */
    protected function _getProductType()
    {
        return Mage::getModel('Magento_Catalog_Model_Product_Type_Configurable');
    }

    /**
     * Check whether prices of configurable products can be editable
     *
     * @return boolean
     */
    public function isAttributesPricesReadonly()
    {
        return $this->getProduct()->getAttributesConfigurationReadonly() ||
            ($this->_catalogData->isPriceGlobal() && $this->isReadonly());
    }

    /**
     * Prepare Layout data
     *
     * @return Magento_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config
     */
    protected function _prepareLayout()
    {
        $this->addChild('create_empty', 'Magento_Adminhtml_Block_Widget_Button', array(
            'label' => __('Create Empty'),
            'class' => 'add',
            'onclick' => 'superProduct.createEmptyProduct()'
        ));
        $this->addChild('super_settings', 'Magento_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Settings');

// @todo: Remove unused code and blocks
//        if ($this->getProduct()->getId()) {
//            $this->setChild('simple',
//                $this->getLayout()->createBlock('Magento_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Simple',
//                    'catalog.product.edit.tab.super.config.simple')
//            );
//
//            $this->addChild('create_from_configurable', 'Magento_Adminhtml_Block_Widget_Button', array(
//                'label' => __('Copy From Configurable'),
//                'class' => 'add',
//                'onclick' => 'superProduct.createNewProduct()'
//            ));
//        }

        $this->addChild(
            'generate',
            'Magento_Backend_Block_Widget_Button',
            array(
                'label' => __('Generate Variations'),
                'class' => 'generate',
                'data_attribute' => array(
                    'mage-init' => array(
                        'button' => array(
                            'event' => 'generate',
                            'target' => '#product-variations-matrix',
                            'eventData' => array(
                                'url' => $this->getUrl('*/*/generateVariations', array('_current' => true)),
                            ),
                        ),
                    ),
                    'action' => 'generate',
                ),
            )
        );
        $this->addChild(
            'add_attribute',
            'Magento_Backend_Block_Widget_Button',
            array(
                'label' => __('Create New Variation Set'),
                'class' => 'new-variation-set',
                'data_attribute' => array(
                    'mage-init' => array(
                        'configurableAttribute' => array(
                            'url' => $this->getUrl(
                                '*/catalog_product_attribute/new',
                                array(
                                    'store' => $this->getProduct()->getStoreId(),
                                    'product_tab' => 'variations',
                                    'popup' => 1,
                                    '_query' => array(
                                        'attribute' => array(
                                            'is_global' => 1,
                                            'frontend_input' => 'select',
                                            'is_configurable' => 1
                                        ),
                                    )
                                )
                            )
                        )
                    )
                ),
            )
        );
        $this->addChild(
            'add_option',
            'Magento_Backend_Block_Widget_Button',
            array(
                'label' => __('Add Option'),
                'class' => 'action- scalable add',
                'data_attribute' => array(
                    'mage-init' => array(
                        'button' => array('event' => 'add-option'),
                    ),
                    'action' => 'add-option',
                ),
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * Retrieve currently edited product object
     *
     * @return Magento_Catalog_Model_Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Retrieve attributes data
     *
     * @return array
     */
    public function getAttributes()
    {
        if (!$this->hasData('attributes')) {
            $attributes = (array)$this->_getProductType()->getConfigurableAttributesAsArray($this->getProduct());
            $productData = (array)$this->getRequest()->getParam('product');
            if (isset($productData['configurable_attributes_data'])) {
                $configurableData = $productData['configurable_attributes_data'];
                foreach ($attributes as $key => &$attribute) {
                    if (isset($configurableData[$key])) {
                        $attribute['values'] = array_merge(
                            isset($attribute['values']) ? $attribute['values'] : array(),
                            isset($configurableData[$key]['values'])
                                ? array_filter($configurableData[$key]['values'])
                                : array()
                        );
                    }
                }
            }

            foreach ($attributes as &$attribute) {
                if (isset($attribute['values']) && is_array($attribute['values'])) {
                    foreach ($attribute['values'] as &$attributeValue) {
                        if (!$this->getCanReadPrice()) {
                            $attributeValue['pricing_value'] = '';
                            $attributeValue['is_percent'] = 0;
                        }
                        $attributeValue['can_edit_price'] = $this->getCanEditPrice();
                        $attributeValue['can_read_price'] = $this->getCanReadPrice();
                    }
                }
            }
            $this->setData('attributes', $attributes);
        }
        return $this->getData('attributes');
    }

    /**
     * Retrieve Links in JSON format
     *
     * @return string
     */
    public function getLinksJson()
    {
        $products = $this->_getProductType()
            ->getUsedProducts($this->getProduct());
        if(!$products) {
            return '{}';
        }
        $data = array();
        foreach ($products as $product) {
            $data[$product->getId()] = $this->getConfigurableSettings($product);
        }
        return $this->_coreData->jsonEncode($data);
    }

    /**
     * Retrieve configurable settings
     *
     * @param Magento_Catalog_Model_Product $product
     * @return array
     */
    public function getConfigurableSettings($product) {
        $data = array();
        $attributes = $this->_getProductType()
            ->getUsedProductAttributes($this->getProduct());
        foreach ($attributes as $attribute) {
            $data[] = array(
                'attribute_id' => $attribute->getId(),
                'label'        => $product->getAttributeText($attribute->getAttributeCode()),
                'value_index'  => $product->getData($attribute->getAttributeCode())
            );
        }

        return $data;
    }

    /**
     * Retrieve Grid child HTML
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    /**
     * Retrieve Grid JavaScript object name
     *
     * @return string
     */
    public function getGridJsObject()
    {
        return $this->getChildBlock('grid')->getJsObjectName();
    }

    /**
     * Retrieve Create New Empty Product URL
     *
     * @return string
     */
    public function getNewEmptyProductUrl()
    {
        return $this->getUrl(
            '*/*/new',
            array(
                'set'      => $this->getProduct()->getAttributeSetId(),
                'type'     => Magento_Catalog_Model_Product_Type::TYPE_SIMPLE,
                'required' => $this->_getRequiredAttributesIds(),
                'popup'    => 1
            )
        );
    }

    /**
     * Retrieve Create New Product URL
     *
     * @return string
     */
    public function getNewProductUrl()
    {
        return $this->getUrl(
            '*/*/new',
            array(
                'set'      => $this->getProduct()->getAttributeSetId(),
                'type'     => Magento_Catalog_Model_Product_Type::TYPE_SIMPLE,
                'required' => $this->_getRequiredAttributesIds(),
                'popup'    => 1,
                'product'  => $this->getProduct()->getId()
            )
        );
    }

    /**
     * Retrieve Required attributes Ids (comma separated)
     *
     * @return string
     */
    protected function _getRequiredAttributesIds()
    {
        $attributesIds = array();
        $configurableAttributes = $this->getProduct()
            ->getTypeInstance()->getConfigurableAttributes($this->getProduct());
        foreach ($configurableAttributes as $attribute) {
            $attributesIds[] = $attribute->getProductAttribute()->getId();
        }

        return implode(',', $attributesIds);
    }

    /**
     * Retrieve Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Associated Products');
    }

    /**
     * Retrieve Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Associated Products');
    }

    /**
     * Can show tab flag
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Check is a hidden tab
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Show "Use default price" checkbox
     *
     * @return bool
     */
    public function getShowUseDefaultPrice()
    {
        return !$this->_catalogData->isPriceGlobal()
            && $this->getProduct()->getStoreId();
    }

    /**
     * Get list of used attributes
     *
     * @return array
     */
    public function getSelectedAttributes()
    {
        return $this->getProduct()->isConfigurable()
            ? array_filter($this->_getProductType()->getUsedProductAttributes($this->getProduct()))
            : array();
    }

    /**
     * Get parent tab code
     *
     * @return string
     */
    public function getParentTab()
    {
        return 'product-details';
    }

    /**
     * @return Magento_Core_Model_App
     */
    public function getApp()
    {
        return $this->_app;
    }

    /**
     * @return Magento_Core_Model_LocaleInterface
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * Get base application currency
     *
     * @return Zend_Currency
     */
    public function getBaseCurrency()
    {
        return $this->getLocale()->currency($this->getApp()->getBaseCurrencyCode());
    }
}
