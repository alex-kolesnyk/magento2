<?php
/**
 * Abstract config form element renderer
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Adminhtml_Block_System_Config_Form_Field
    extends Mage_Core_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<tr><td class="label">'.$element->getLabel().'</td>';
        
        $id = $element->getHtmlId();
        $isDefault = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $isMultiple = $element->getExtType()==='multiple';

        // replace [value] with [inherit]
        $namePrefix = substr($element->getName(), 0, strlen($element->getName())-7);

        $inherit = $element->getInherit()==1 ? 'checked' : '';
        $options = $element->getValues();
        
        if ($inherit) {
            $element->setDisabled(true);
        }

        if ($isMultiple) {
            $element->setName($element->getName().'[]');
        }

        $html.= '<td class="value">'.$element->getElementHtml().'</td>';
        
        if (!$isDefault) {
            $defText = $element->getDefaultValue();
            if ($options) {
                $defTextArr = array();
                foreach ($options as $k=>$v) {
                    if ($isMultiple) {
                        if (is_array($v['value']) && in_array($k, $v['value'])) {
                            $defTextArr[] = $v['label'];
                        }
                    } elseif ($v['value']==$defText) {
                        $defTextArr[] = $v['label'];
                        break;
                    }
                }
                $defText = join(', ', $defTextArr);
            }
   
            // default value
            $html.= '<td class="default">';
            $html.= '<input id="'.$id.'_inherit" name="'.$namePrefix.'[inherit]" type="checkbox" value="1" class="input-checkbox config-inherit" '.$inherit.' onclick="$(\''.$id.'\').disabled = this.checked">';
            $html.= '<label for="'.$id.'_inherit" class="inherit" title="'.htmlspecialchars($defText).'">'.__('Use default').'</label>';
            $html.= '<input type="hidden" name="'.$namePrefix.'[default_value]" value="'.htmlspecialchars($element->getDefaultValue()).'">';
            $html.= '<input type="hidden" name="'.$namePrefix.'[old_value]" value="'.htmlspecialchars($element->getOldValue()).'">';
            $html.= '</td>';
        }

        $html.= '</tr>';
        return $html;
    }
}
