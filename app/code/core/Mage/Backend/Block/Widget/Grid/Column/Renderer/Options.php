<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Grid column widget for rendering grid cells that contains mapped values
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Block_Widget_Grid_Column_Renderer_Options
    extends Mage_Backend_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * Render a grid cell as options
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $options = $this->getColumn()->getOptions();
        $showMissingOptionValues = (bool)$this->getColumn()->getShowMissingOptionValues();
        if (!empty($options) && is_array($options)) {
            $value = $row->getData($this->getColumn()->getIndex());
            if (is_array($value)) {
                $res = array();
                foreach ($value as $item) {
                    if (isset($options[$item])) {
                        $res[] = $this->escapeHtml($options[$item]);
                    }
                    elseif ($showMissingOptionValues) {
                        $res[] = $this->escapeHtml($item);
                    }
                }
                return implode(', ', $res);
            } elseif (isset($options[$value])) {
                return $this->escapeHtml($options[$value]);
            } elseif (in_array($value, $options)) {
                return $this->escapeHtml($value);
            }
        }
    }
}
