<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * @method \Magento\Backend\Block\System\Config\Form getForm()
 */
namespace Magento\Backend\Block\System\Config\Form\Fieldset\Modules;

class DisableOutput
    extends \Magento\Backend\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Magento\Object
     */
    protected $_dummyElement;


    /**
     * @var \Magento\Backend\Block\System\Config\Form\Field
     */
    protected $_fieldRenderer;

    /**
     * @var array
     */
    protected $_values;

    /**
     * @var \Magento\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Core\Helper\Js $jsHelper
     * @param \Magento\Module\ModuleListInterface $moduleList
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Core\Helper\Js $jsHelper,
        \Magento\Module\ModuleListInterface $moduleList,
        array $data = array()
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->_moduleList = $moduleList;
    }

    /**
     * @param \Magento\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

        $modules = array_keys($this->_moduleList->getModules());

        $dispatchResult = new \Magento\Object($modules);
        $this->_eventManager->dispatch('adminhtml_system_config_advanced_disableoutput_render_before',
            array('modules' => $dispatchResult)
        );
        $modules = $dispatchResult->toArray();

        sort($modules);

        foreach ($modules as $moduleName) {
            if ($moduleName === 'Magento_Adminhtml' || $moduleName === 'Magento_Backend') {
                continue;
            }
            $html.= $this->_getFieldHtml($element, $moduleName);
        }
        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    /**
     * @return \Magento\Object
     */
    protected function _getDummyElement()
    {
        if (empty($this->_dummyElement)) {
            $this->_dummyElement = new \Magento\Object(array('showInDefault' => 1, 'showInWebsite' => 1));
        }
        return $this->_dummyElement;
    }

    /**
     * @return \Magento\Backend\Block\System\Config\Form\Field
     */
    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = $this->_layout->getBlockSingleton('Magento\Backend\Block\System\Config\Form\Field');
        }
        return $this->_fieldRenderer;
    }

    /**
     * @return array
     */
    protected function _getValues()
    {
        if (empty($this->_values)) {
            $this->_values = array(
                array('label' => __('Enable'), 'value' => 0),
                array('label' => __('Disable'), 'value' => 1),
            );
        }
        return $this->_values;
    }

    /**
     * @param \Magento\Data\Form\Element\Fieldset $fieldset
     * @param string $moduleName
     * @return mixed
     */
    protected function _getFieldHtml($fieldset, $moduleName)
    {
        $configData = $this->getConfigData();
        $path = 'advanced/modules_disable_output/' . $moduleName; //TODO: move as property of form
        if (isset($configData[$path])) {
            $data = $configData[$path];
            $inherit = false;
        } else {
            $data = (int)(string)$this->getForm()->getConfigValue($path);
            $inherit = true;
        }

        $element = $this->_getDummyElement();

        $field = $fieldset->addField($moduleName, 'select',
            array(
                'name'          => 'groups[modules_disable_output][fields]['.$moduleName.'][value]',
                'label'         => $moduleName,
                'value'         => $data,
                'values'        => $this->_getValues(),
                'inherit'       => $inherit,
                'can_use_default_value' => $this->getForm()->canUseDefaultValue($element),
                'can_use_website_value' => $this->getForm()->canUseWebsiteValue($element),
            ))->setRenderer($this->_getFieldRenderer());

        return $field->toHtml();
    }
}
