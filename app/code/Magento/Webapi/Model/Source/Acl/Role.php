<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Webapi
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Web API Role source model.
 *
 * @category    Magento
 * @package     Magento_Webapi
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Webapi\Model\Source\Acl;

class Role implements \Magento\Core\Model\Option\ArrayInterface
{
    /**
     * @var \Magento\Webapi\Model\Resource\Acl\Role
     */
    protected $_resource = null;

    /**
     * @param \Magento\Webapi\Model\Resource\Acl\RoleFactory $roleFactory
     */
    public function __construct(
        \Magento\Webapi\Model\Resource\Acl\RoleFactory $roleFactory
    ) {
        $this->_resource = $roleFactory->create();
    }

    /**
     * Retrieve option hash of Web API Roles.
     *
     * @param bool $addEmpty
     * @return array
     */
    public function toOptionHash($addEmpty = true)
    {
        $options = $this->_resource->getRolesList();
        if ($addEmpty) {
            $options = array('' => '') + $options;
        }
        return $options;
    }

    /**
     * Return option array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->_resource->getRolesList();
        return $options;
    }
}
