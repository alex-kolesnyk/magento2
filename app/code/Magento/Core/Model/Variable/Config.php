<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Variable Wysiwyg Plugin Config
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Core_Model_Variable_Config
{
    /**
     * @var Magento_Core_Model_View_Url
     */
    protected $_viewUrl;

    /**
     * @param Magento_Core_Model_View_Url $viewUrl
     */
    public function __construct(Magento_Core_Model_View_Url $viewUrl)
    {
        $this->_viewUrl = $viewUrl;
    }

    /**
     * Prepare variable wysiwyg config
     *
     * @param Magento_Object $config
     * @return array
     */
    public function getWysiwygPluginSettings($config)
    {
        $variableConfig = array();
        $onclickParts = array(
            'search' => array('html_id'),
            'subject' => 'MagentovariablePlugin.loadChooser(\'' . $this->getVariablesWysiwygActionUrl()
                . '\', \'{{html_id}}\');'
        );
        $variableWysiwyg = array(array('name' => 'magentovariable',
            'src' => $this->getWysiwygJsPluginSrc(),
            'options' => array(
                'title' => __('Insert Variable...'),
                'url' => $this->getVariablesWysiwygActionUrl(),
                'onclick' => $onclickParts,
                'class'   => 'add-variable plugin'
        )));
        $configPlugins = $config->getData('plugins');
        $variableConfig['plugins'] = array_merge($configPlugins, $variableWysiwyg);
        return $variableConfig;
    }

    /**
     * Return url to wysiwyg plugin
     *
     * @return string
     */
    public function getWysiwygJsPluginSrc()
    {
        $editorPluginJs = 'mage/adminhtml/wysiwyg/tiny_mce/plugins/magentovariable/editor_plugin.js';
        return $this->_viewUrl->getViewFileUrl($editorPluginJs);
    }

    /**
     * Return url of action to get variables
     *
     * @return string
     */
    public function getVariablesWysiwygActionUrl()
    {
        return Mage::getSingleton('Magento_Backend_Model_Url')->getUrl('*/system_variable/wysiwygPlugin');
    }
}
