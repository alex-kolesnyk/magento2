<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_GoogleShopping
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Attributes Factory
 *
 * @category   Magento
 * @package    Magento_GoogleShopping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GoogleShopping\Model;

class AttributeFactory
{
    /**
     * Object manager
     *
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * GoogleShopping data
     *
     * @var \Magento\GoogleShopping\Helper\Data
     */
    protected $_gsData;

    /**
     * @var \Magento\Stdlib\String
     */
    protected $_string;

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\GoogleShopping\Helper\Data $gsData
     * @param \Magento\Stdlib\String $string
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        \Magento\GoogleShopping\Helper\Data $gsData,
        \Magento\Stdlib\String $string
    ) {
        $this->_objectManager = $objectManager;
        $this->_gsData = $gsData;
        $this->_string = $string;
    }

    /**
     * Create attribute model
     *
     * @param string $name
     * @return \Magento\GoogleShopping\Model\Attribute\DefaultAttribute
     */
    public function createAttribute($name)
    {
        $modelName = 'Magento\GoogleShopping\Model\Attribute\\'
            . $this->_string->upperCaseWords($this->_gsData->normalizeName($name));
        try {
            /** @var \Magento\GoogleShopping\Model\Attribute\DefaultAttribute $attributeModel */
            $attributeModel = $this->_objectManager->create($modelName);
            if (!$attributeModel) {
                $attributeModel = $this->_objectManager
                    ->create('Magento\GoogleShopping\Model\Attribute\DefaultAttribute');
            }
        } catch (\Exception $e) {
            $attributeModel = $this->_objectManager
                ->create('Magento\GoogleShopping\Model\Attribute\DefaultAttribute');
        }

        $attributeModel->setName($name);
        return $attributeModel;
    }

    /**
     * Create attribute model
     *
     * @return \Magento\GoogleShopping\Model\Attribute
     */
    public function create()
    {
        return $this->_objectManager->create('Magento\GoogleShopping\Model\Attribute');
    }
}
