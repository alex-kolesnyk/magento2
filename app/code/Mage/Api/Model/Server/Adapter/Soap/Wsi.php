<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Api
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * SOAP WS-I compatible adapter
 *
 * @category   Mage
 * @package    Mage_Api
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api_Model_Server_Adapter_Soap_Wsi extends Mage_Api_Model_Server_Adapter_Soap
{
    /**
     * Run webservice
     *
     * @param Mage_Api_Controller_Action $controller
     * @return Mage_Api_Model_Server_Adapter_Soap
     */
    public function run()
    {
        $apiConfigCharset = Mage::getStoreConfig("api/config/charset");

        if ($this->getController()->getRequest()->getParam('wsdl') !== null) {
            $wsdlConfig = Mage::getModel('Mage_Api_Model_Wsdl_Config');
            $wsdlConfig->setHandler($this->getHandler())
                ->setCacheId('wsdl_config_global_soap_wsi')
                ->init();
            $this->getController()->getResponse()
                ->clearHeaders()
                ->setHeader('Content-Type', 'text/xml; charset=' . $apiConfigCharset)
                ->setBody(
                preg_replace(
                    '/(\>\<)/i',
                    ">\n<",
                    str_replace(
                        '<soap:operation soapAction=""></soap:operation>',
                        "<soap:operation soapAction=\"\" />\n",
                        str_replace(
                            '<soap:body use="literal"></soap:body>',
                            "<soap:body use=\"literal\" />\n",
                            preg_replace(
                                '/<\?xml version="([^\"]+)"([^\>]+)>/i',
                                '<?xml version="$1" encoding="' . $apiConfigCharset . '"?>',
                                $wsdlConfig->getWsdlContent()
                            )
                        )
                    )
                )
            );
        } else {
            try {
                $this->_instantiateServer();

                $content = preg_replace(
                    '/(\>\<)/i',
                    ">\n<",
                    str_replace(
                        '<soap:operation soapAction=""></soap:operation>',
                        "<soap:operation soapAction=\"\" />\n",
                        str_replace(
                            '<soap:body use="literal"></soap:body>',
                            "<soap:body use=\"literal\" />\n",
                            preg_replace(
                                '/<\?xml version="([^\"]+)"([^\>]+)>/i',
                                '<?xml version="$1" encoding="' . $apiConfigCharset . '"?>',
                                $this->_soap->handle()
                             )
                         )
                    )
                );

                $this->getController()->getResponse()
                    ->clearHeaders()
                    ->setHeader('Content-Type', 'text/xml; charset=' . $apiConfigCharset)
                    ->setHeader('Content-Length', strlen($content), true)
                    ->setBody($content);
            } catch (Zend_Soap_Server_Exception $e) {
                $this->fault($e->getCode(), $e->getMessage());
            } catch (Exception $e) {
                $this->fault($e->getCode(), $e->getMessage());
            }
        }

        return $this;
    }
}
