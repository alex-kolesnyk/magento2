<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Webapi
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Api Acl Resource Loader
 *
 * @category    Mage
 * @package     Mage_Webapi
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Webapi_Model_Authorization_Loader_Resource implements Magento_Acl_Loader
{
    /**
     * Acl config
     *
     * @var Mage_Webapi_Model_Authorization_Config
     */
    protected $_config;

    /**
     * Application object factory
     *
     * @var Mage_Core_Model_Config
     */
    protected $_objectFactory;

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->_config = isset($data['config']) ? $data['config'] :
            Mage::getSingleton('Mage_Webapi_Model_Authorization_Config');
        $this->_objectFactory = isset($data['objectFactory']) ? $data['objectFactory'] : Mage::getConfig();
    }

    /**
     * Populate ACL with resources from external storage
     *
     * @param Magento_Acl $acl
     */
    public function populateAcl(Magento_Acl $acl)
    {
        $this->_addResourceTree($acl, $this->_config->getAclResources(), null);
    }

    /**
     * Add list of nodes and their children to acl
     *
     * @param Magento_Acl $acl
     * @param DOMNodeList $resources
     * @param Magento_Acl_Resource $parent
     */
    protected function _addResourceTree(Magento_Acl $acl, DOMNodeList $resources, Magento_Acl_Resource $parent = null)
    {
        /** @var $resourceConfig DOMElement */
        foreach ($resources as $resourceConfig) {
            if (!($resourceConfig instanceof DOMElement)) {
                continue;
            }
            /** @var $resource Magento_Acl_Resource */
            $resource = $this->_objectFactory->getModelInstance(
                'Magento_Acl_Resource',
                $resourceConfig->getAttribute('id')
            );
            $acl->addResource($resource, $parent);
            if ($resourceConfig->hasChildNodes()) {
                $this->_addResourceTree($acl, $resourceConfig->childNodes, $resource);
            }
        }
    }
}
