<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_TargetRule
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * TargetRule Action Product Price (percentage) Condition Model
 *
 * @category   Magento
 * @package    Magento_TargetRule
 */
namespace Magento\TargetRule\Model\Actions\Condition\Product\Special;

class Price
    extends \Magento\TargetRule\Model\Actions\Condition\Product\Special
{
    /**
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Resource\Product $productResource
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection $attrSetCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Model\Resource\Product $productResource,
        \Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection $attrSetCollection,
        array $data = array()
    ) {
        parent::__construct(
            $backendData, $context, $config, $product, $productResource, $attrSetCollection, $data
        );
        $this->setType('Magento\TargetRule\Model\Actions\Condition\Product\Special\Price');
        $this->setValue(100);
    }

    /**
     * Retrieve operator select options array
     *
     * @return array
     */
    protected function _getOperatorOptionArray()
    {
        return array(
            '==' => __('equal to'),
            '>'  => __('more'),
            '>=' => __('equals or greater than'),
            '<'  => __('less'),
            '<=' => __('equals or less than')
        );
    }

    /**
     * Set operator options
     *
     * @return \Magento\TargetRule\Model\Actions\Condition\Product\Special\Price
     */
    public function loadOperatorOptions()
    {
        parent::loadOperatorOptions();
        $this->setOperatorOption($this->_getOperatorOptionArray());
        return $this;
    }

    /**
     * Retrieve rule as HTML formated string
     *
     * @return string
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml()
            . __('Product Price is %1 %2% of Matched Product(s) Price', $this->getOperatorElementHtml(), $this->getValueElementHtml())
            . $this->getRemoveLinkHtml();
    }

    /**
     * Retrieve SELECT WHERE condition for product collection
     *
     * @param \Magento\Catalog\Model\Resource\Product\Collection $collection
     * @param \Magento\TargetRule\Model\Index $object
     * @param array $bind
     * @return \Zend_Db_Expr
     */
    public function getConditionForCollection($collection, $object, &$bind)
    {
        /* @var $resource \Magento\TargetRule\Model\Resource\Index */
        $resource       = $object->getResource();
        $operator       = $this->getOperator();

        $where = $resource->getOperatorBindCondition('price_index.min_price', 'final_price', $operator, $bind,
            array(array('bindPercentOf', $this->getValue())));
        return new \Zend_Db_Expr(sprintf('(%s)', $where));
    }
}
