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
 * Poll edit form
 */

namespace Magento\Adminhtml\Block\Rating\Edit\Tab;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Store manager instance
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Data\Form\Factory $formFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Data\Form\Factory $formFactory,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($coreRegistry, $formFactory, $coreData, $context, $data);
    }


    /**
     * Prepare rating edit form
     *
     * @return \Magento\Adminhtml\Block\Rating\Edit\Tab\Form
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Data\Form $form */
        $form   = $this->_formFactory->create();
        $this->setForm($form);

        $fieldset = $form->addFieldset('rating_form', array(
            'legend'=>__('Rating Title')
        ));

        $fieldset->addField('rating_code', 'text', array(
            'name' => 'rating_code',
            'label' => __('Default Value'),
            'class' => 'required-entry',
            'required' => true,
        ));

        foreach (\Mage::getSingleton('Magento\Core\Model\System\Store')->getStoreCollection() as $store) {
            $fieldset->addField('rating_code_' . $store->getId(), 'text', array(
                'label' => $store->getName(),
                'name' => 'rating_codes[' . $store->getId() . ']',
            ));
        }

        if (\Mage::getSingleton('Magento\Adminhtml\Model\Session')->getRatingData()) {
            $form->setValues(\Mage::getSingleton('Magento\Adminhtml\Model\Session')->getRatingData());
            $data = \Mage::getSingleton('Magento\Adminhtml\Model\Session')->getRatingData();
            if (isset($data['rating_codes'])) {
               $this->_setRatingCodes($data['rating_codes']);
            }
            \Mage::getSingleton('Magento\Adminhtml\Model\Session')->setRatingData(null);
        } elseif ($this->_coreRegistry->registry('rating_data')) {
            $form->setValues($this->_coreRegistry->registry('rating_data')->getData());
            if ($this->_coreRegistry->registry('rating_data')->getRatingCodes()) {
               $this->_setRatingCodes($this->_coreRegistry->registry('rating_data')->getRatingCodes());
            }
        }

        if ($this->_coreRegistry->registry('rating_data')) {
            $collection = \Mage::getModel('Magento\Rating\Model\Rating\Option')
                ->getResourceCollection()
                ->addRatingFilter($this->_coreRegistry->registry('rating_data')->getId())
                ->load();

            $i = 1;
            foreach ($collection->getItems() as $item) {
                $fieldset->addField('option_code_' . $item->getId() , 'hidden', array(
                    'required' => true,
                    'name' => 'option_title[' . $item->getId() . ']',
                    'value' => ($item->getCode()) ? $item->getCode() : $i,
                ));

                $i ++;
            }
        } else {
            for ($i = 1; $i <= 5; $i++) {
                $fieldset->addField('option_code_' . $i, 'hidden', array(
                    'required' => true,
                    'name' => 'option_title[add_' . $i . ']',
                    'value' => $i,
                ));
            }
        }

        $fieldset = $form->addFieldset('visibility_form', array(
            'legend' => __('Rating Visibility')
        ));
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField('stores', 'multiselect', array(
                'label' => __('Visible In'),
                'name' => 'stores[]',
                'values' => \Mage::getSingleton('Magento\Core\Model\System\Store')->getStoreValuesForForm(),
            ));
            $renderer = $this->getLayout()->createBlock('Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element');
            $field->setRenderer($renderer);

            if ($this->_coreRegistry->registry('rating_data')) {
                $form->getElement('stores')->setValue($this->_coreRegistry->registry('rating_data')->getStores());
            }
        }

        $fieldset->addField('is_active', 'checkbox', array(
            'label' => __('Is Active'),
            'name' => 'is_active',
            'value' => 1,
        ));

        $fieldset->addField('position', 'text', array(
            'label' => __('Sort Order'),
            'name' => 'position',
        ));

        if ($this->_coreRegistry->registry('rating_data')) {
            $form->getElement('position')->setValue($this->_coreRegistry->registry('rating_data')->getPosition());
            $form->getElement('is_active')->setIsChecked($this->_coreRegistry->registry('rating_data')->getIsActive());
        }

        return parent::_prepareForm();
    }

    protected function _setRatingCodes($ratingCodes)
    {
        foreach($ratingCodes as $store=>$value) {
            $element = $this->getForm()->getElement('rating_code_' . $store);
            if ($element) {
               $element->setValue($value);
            }
        }
    }

    protected function _toHtml()
    {
        return $this->_getWarningHtml() . parent::_toHtml();
    }

    protected function _getWarningHtml()
    {
        return '<div>
<ul class="messages">
    <li class="notice-msg">
        <ul>
            <li>' . __('Please specify a rating title for a store, or we\'ll just use the default value.') . '</li>
        </ul>
    </li>
</ul>
</div>';
    }
}
