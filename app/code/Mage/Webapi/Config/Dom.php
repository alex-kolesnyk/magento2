<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Webapi_Config_Dom extends Magento_Config_Dom
{

    /**
     * Getter for node by path
     *
     * @param string $nodePath
     * @throws Magento_Exception an exception is possible if original document contains multiple fixed nodes
     * @return DOMElement | null
     */
    protected function _getMatchedNode($nodePath)
    {
        if (!preg_match('/^\/service?$/i', $nodePath)) {
            return null;
        }
        return parent::_getMatchedNode($nodePath);
    }
}
