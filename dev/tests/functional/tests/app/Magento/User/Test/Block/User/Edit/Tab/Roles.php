<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\User\Test\Block\User\Edit\Tab;

use Mtf\Client\Element;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;
use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Class Roles
 * Grid on Roles Tab page for User
 *
 * @package Magento\User\Test\Block\User\Edit\Tab
 */
class Roles extends Grid
{
    /**
     * Initialize grid elements
     */
    protected function _init()
    {
        parent::_init();
        $this->filters = array(
            'id' => array(
                'selector' => '#permissionsUserRolesGrid_filter_assigned_user_role',
                'input' => 'select'
            ),
            'role_name' => array(
                'selector' => '#permissionsUserRolesGrid_filter_role_name'
            )
        );
    }

    /**
     * Choose role on grid during user edit/create
     */
    public function setRole($roleName, $roleId)
    {
        $this->search(array('role_name' => $roleName));
        $rowItem = $this->_rootElement->find($this->rowItem, Locator::SELECTOR_CSS);
        $rowItem->find("//input[@value='$roleId']", Locator::SELECTOR_XPATH)->click();
    }
}

