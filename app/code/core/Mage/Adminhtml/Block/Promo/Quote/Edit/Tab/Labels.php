<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Adminhtml_Block_Promo_Quote_Edit_Tab_Labels
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Application instance
     *
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * Class constructor
     *
     * @param array $data
     * @throws InvalidArgumentException
     */
    public function __construct(array $data = array())
    {
        $this->_app = isset($data['app']) ? $data['app'] : Mage::app();

        if (!($this->_app instanceof Mage_Core_Model_App)) {
            throw new InvalidArgumentException('Required application object is invalid');
        }
        parent::__construct($data);
    }

    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('Mage_SalesRule_Helper_Data')->__('Labels');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('Mage_SalesRule_Helper_Data')->__('Labels');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $rule = Mage::registry('current_promo_quote_rule');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('default_label_fieldset', array(
            'legend' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Default Label')
        ));
        $labels = $rule->getStoreLabels();

        $fieldset->addField('store_default_label', 'text', array(
            'name'      => 'store_labels[0]',
            'required'  => false,
            'label'     => Mage::helper('Mage_SalesRule_Helper_Data')->__('Default Rule Label for All Store Views'),
            'value'     => isset($labels[0]) ? $labels[0] : '',
        ));

        if (!$this->_app->isSingleStoreMode()) {
            $fieldset = $this->_createStoreSpecificFieldset($form, $labels);
        }

        if ($rule->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Create store specific fieldset
     *
     * @param Varien_Data_Form $form
     * @param array $labels
     * @return Varien_Data_Form_Element_Fieldset mixed
     */
    protected function _createStoreSpecificFieldset($form, $labels)
    {
        $fieldset = $form->addFieldset('store_labels_fieldset', array(
            'legend' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Store View Specific Labels'),
            'table_class' => 'form-list stores-tree',
        ));
        $renderer = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Store_Switcher_Form_Renderer_Fieldset');
        $fieldset->setRenderer($renderer);

        foreach (Mage::app()->getWebsites() as $website) {
            $fieldset->addField("w_{$website->getId()}_label", 'note', array(
                'label' => $website->getName(),
                'fieldset_html_class' => 'website',
            ));
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField("sg_{$group->getId()}_label", 'note', array(
                    'label' => $group->getName(),
                    'fieldset_html_class' => 'store-group',
                ));
                foreach ($stores as $store) {
                    $fieldset->addField("s_{$store->getId()}", 'text', array(
                        'name' => 'store_labels[' . $store->getId() . ']',
                        'required' => false,
                        'label' => $store->getName(),
                        'value' => isset($labels[$store->getId()]) ? $labels[$store->getId()] : '',
                        'fieldset_html_class' => 'store',
                    ));
                }
            }
        }
        return $fieldset;
    }
}
