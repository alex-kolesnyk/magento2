<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_CatalogRule
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Catalog Rule Combine Condition data model
 */
namespace Magento\CatalogRule\Model\Rule\Condition;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\ProductFactory
     */
    protected $_conditionFactory;

    /**
     * @param \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionFactory
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionFactory,
        \Magento\Rule\Model\Condition\Context $context,
        array $data = array()
    ) {
        $this->_conditionFactory = $conditionFactory;
        parent::__construct($context, $data);
        $this->setType('Magento\CatalogRule\Model\Rule\Condition\Combine');
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->_conditionFactory->create()
            ->loadAttributeOptions()
            ->getAttributeOption();
        $attributes = array();
        foreach ($productAttributes as $code => $label) {
            $attributes[] = array(
                'value' => 'Magento_CatalogRule_Model_Rule_Condition_Product|' . $code, 'label' => $label
            );
        }
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array(
                'value' => 'Magento\CatalogRule\Model\Rule\Condition\Combine',
                'label' => __('Conditions Combination')
            ),
            array(
                'label' => __('Product Attribute'),
                'value' => $attributes
            ),
        ));
        return $conditions;
    }

    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}
