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
 * Quick simple product creation
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Super\Config;

class Simple
    extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes
{
    /**
     * Link to currently editing product
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Data\Form\Factory $formFactory
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Data\Form\Factory $formFactory,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\Registry $registry,
        array $data = array()
    ) {
        $this->_productFactory = $productFactory;
        parent::__construct($wysiwygConfig, $formFactory, $catalogData, $coreData, $context, $registry, $data);
    }

    protected function _prepareForm()
    {
        /** @var \Magento\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setFieldNameSuffix('simple_product');
        $form->setDataObject($this->getProduct());

        $fieldset = $form->addFieldset('simple_product', array(
            'legend' => __('Quick simple product creation')
        ));
        $this->_addElementTypes($fieldset);
        $attributesConfig = array(
            'autogenerate' => array('name', 'sku'),
            'additional'   => array('name', 'sku', 'visibility', 'status')
        );

        $availableTypes = array('text', 'select', 'multiselect', 'textarea', 'price', 'weight');

        $attributes = $this->_productFactory->create()
            ->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
            ->setAttributeSetId($this->getProduct()->getAttributeSetId())
            ->getAttributes();

        /* Standard attributes */
        foreach ($attributes as $attribute) {
            if (($attribute->getIsRequired()
                && $attribute->getApplyTo()
                // If not applied to configurable
                && !in_array(\Magento\Catalog\Model\Product\Type::TYPE_CONFIGURABLE, $attribute->getApplyTo())
                // If not used in configurable
                && !in_array($attribute->getId(),
                    $this->getProduct()->getTypeInstance()->getUsedProductAttributeIds($this->getProduct()))
                )
                // Or in additional
                || in_array($attribute->getAttributeCode(), $attributesConfig['additional'])
            ) {
                $inputType = $attribute->getFrontend()->getInputType();
                if (!in_array($inputType, $availableTypes)) {
                    continue;
                }
                $attributeCode = $attribute->getAttributeCode();
                $attribute->setAttributeCode('simple_product_' . $attributeCode);
                $element = $fieldset->addField(
                    'simple_product_' . $attributeCode,
                     $inputType,
                     array(
                        'label'    => $attribute->getFrontend()->getLabel(),
                        'name'     => $attributeCode,
                        'required' => $attribute->getIsRequired(),
                     )
                )->setEntityAttribute($attribute);

                if (in_array($attributeCode, $attributesConfig['autogenerate'])) {
                    $element->setDisabled('true');
                    $element->setValue($this->getProduct()->getData($attributeCode));
                    $element->setAfterElementHtml(
                         '<input type="checkbox" id="simple_product_' . $attributeCode . '_autogenerate" '
                         . 'name="simple_product[' . $attributeCode . '_autogenerate]" value="1" '
                         . 'onclick="toggleValueElements(this, this.parentNode)" checked="checked" /> '
                         . '<label for="simple_product_' . $attributeCode . '_autogenerate" >'
                         . __('Autogenerate')
                         . '</label>'
                    );
                }


                if ($inputType == 'select' || $inputType == 'multiselect') {
                    $element->setValues($attribute->getFrontend()->getSelectOptions());
                }
            }

        }

        /* Configurable attributes */
        $usedAttributes = $this->getProduct()->getTypeInstance()->getUsedProductAttributes($this->getProduct());
        foreach ($usedAttributes as $attribute) {
            $attributeCode =  $attribute->getAttributeCode();
            $fieldset->addField( 'simple_product_' . $attributeCode, 'select',  array(
                'label' => $attribute->getFrontend()->getLabel(),
                'name'  => $attributeCode,
                'values' => $attribute->getSource()->getAllOptions(true, true),
                'required' => true,
                'class'    => 'validate-configurable',
                'onchange' => 'superProduct.showPricing(this, \'' . $attributeCode . '\')'
            ));

            $fieldset->addField('simple_product_' . $attributeCode . '_pricing_value', 'hidden', array(
                'name' => 'pricing[' . $attributeCode . '][value]'
            ));

            $fieldset->addField('simple_product_' . $attributeCode . '_pricing_type', 'hidden', array(
                'name' => 'pricing[' . $attributeCode . '][is_percent]'
            ));
        }

        /* Inventory Data */
        $fieldset->addField('simple_product_inventory_qty', 'text', array(
            'label' => __('Qty'),
            'name'  => 'stock_data[qty]',
            'class' => 'validate-number',
            'required' => true,
            'value'  => 0
        ));

        $fieldset->addField('simple_product_inventory_is_in_stock', 'select', array(
            'label' => __('Stock Availability'),
            'name'  => 'stock_data[is_in_stock]',
            'values' => array(
                array('value'=>1, 'label'=> __('In Stock')),
                array('value'=>0, 'label'=> __('Out of Stock'))
            ),
            'value' => 1
        ));

        $stockHiddenFields = array(
            'use_config_min_qty'            => 1,
            'use_config_min_sale_qty'       => 1,
            'use_config_max_sale_qty'       => 1,
            'use_config_backorders'         => 1,
            'use_config_notify_stock_qty'   => 1,
            'is_qty_decimal'                => 0
        );

        foreach ($stockHiddenFields as $fieldName=>$fieldValue) {
            $fieldset->addField('simple_product_inventory_' . $fieldName, 'hidden', array(
                'name'  => 'stock_data[' . $fieldName .']',
                'value' => $fieldValue
            ));
        }

        $this->setForm($form);
    }

    /**
     * Retrieve currently edited product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('current_product');
        }
        return $this->_product;
    }
}
