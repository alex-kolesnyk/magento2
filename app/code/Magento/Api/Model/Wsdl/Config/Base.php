<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Api
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Wsdl base config
 *
 * @category   Magento
 * @package    Magento_Api
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Magento_Api_Model_Wsdl_Config_Base extends Magento_Simplexml_Config
{
    protected $_handler = '';

    /**
     * @var Magento_Object
     */
    protected $_wsdlVariables = null;

    protected $_loadedFiles = array();

    /**
     * @param null $sourceData
     */
    public function __construct($sourceData = null)
    {
        $this->_elementClass = 'Magento_Api_Model_Wsdl_Config_Element';

        // remove wsdl parameter from query
        $queryParams = Mage::app()->getRequest()->getQuery();
        unset($queryParams['wsdl']);

        // set up default WSDL template variables
        $this->_wsdlVariables = new Magento_Object(
            array(
                'name' => 'Magento',
                'url'  => htmlspecialchars(Mage::getUrl('*/*/*', array('_query' => $queryParams)))
            )
        );
        parent::__construct($sourceData);
    }

    /**
     * Set handler
     *
     * @param string $handler
     * @return Magento_Api_Model_Wsdl_Config_Base
     */
    public function setHandler($handler)
    {
        $this->_handler = $handler;
        return $this;
    }

    /**
     * Get handler
     *
     * @return string
     */
    public function getHandler()
    {
        return $this->_handler;
    }

    /**
     * Processing file data
     *
     * @param string $text
     * @return string
     */
    public function processFileData($text)
    {
        /** @var $template Magento_Core_Model_Email_Template_Filter */
        $template = Mage::getModel('Magento_Core_Model_Email_Template_Filter');

        $this->_wsdlVariables->setHandler($this->getHandler());

        $template->setVariables(array('wsdl'=>$this->_wsdlVariables));

        return $template->filter($text);
    }

    public function addLoadedFile($file)
    {
        if (!in_array($file, $this->_loadedFiles)) {
            $this->_loadedFiles[] = $file;
        }
        return $this;
    }

    public function loadFile($file)
    {
        if (in_array($file, $this->_loadedFiles)) {
            return false;
        }
        $res = parent::loadFile($file);
        if ($res) {
            $this->addLoadedFile($file);
        }
        return $this;
    }

    /**
     * Set variable to be used in WSDL template processing
     *
     * @param string $key Varible key
     * @param string $value Variable value
     * @return Magento_Api_Model_Wsdl_Config_Base
     */
    public function setWsdlVariable($key, $value)
    {
        $this->_wsdlVariables->setData($key, $value);

        return $this;
    }
}
