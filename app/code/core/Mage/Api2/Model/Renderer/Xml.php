<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Api2
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Webservice apia2 renderer of XML type model
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Renderer_Xml implements Mage_Api2_Model_Renderer_Interface
{
    /**
     * Convert Array to XML
     *
     * @param array $data
     * @param null $options
     * @return string
     */
    public function render(array $data, $options = null)
    {
        $value = Zend_XmlRpc_Value::getXmlRpcValue($data);

        $generator = Zend_XmlRpc_Value::getGenerator();
        $generator->openElement('methodResponse')
                  ->openElement('params')
                  ->openElement('param');
        $value->generateXml();
        $generator->closeElement('param')
                  ->closeElement('params')
                  ->closeElement('methodResponse');

        $content = $generator->flush();

        if (isset($options['encoding'])) {
            $content = preg_replace(
                '/<\?xml version="([^\"]+)"([^\>]+)>/i',
                '<?xml version="$1" encoding="'.$options['encoding'].'"?>',
                $content
            );
        }

        return $content;
    }

    /**
     * Render error content
     *
     * @param int $code
     * @param array $exceptions
     * @return string
     */
    public function renderErrors($code, $exceptions)
    {
        $content = '<messages>
    <error>
        <domain>:domain</domain>
        <code>:code</code>
        <message>:message</message>
        <extended>:extended</extended>
    </error>
</messages>';

        $domain = 'core';
        $code = 123;
        $message = 'random_string';
        $extended = 'Resource just randomly throw test errors.';
        $replace = array(
            ':domain'   => $domain,
            ':code'     => $code,
            ':message'  => $message,
            ':extended' => $extended,
        );
        $content = strtr($content, $replace);

        return preg_replace('/(\>\<)/i', ">\n<", $content);
    }
}
