<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Catalog products per page on Grid mode source
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Catalog_Model_Config_Source_GridPerPage implements Magento_Core_Model_Option_ArrayInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @param string $perPageValues
     */
    public function __construct($perPageValues)
    {
        $this->_options = explode(',', $perPageValues);
    }

    public function toOptionArray()
    {
        $result = array();
        foreach ($this->_options as $option) {
            $result[] = array('value' => $option, 'label' => $option);
        }
        return $result;
    }
}
