<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * \Magento\User roles controller
 */
namespace Magento\User\Controller\Adminhtml\User;

class Role extends \Magento\Backend\App\AbstractAction
{

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Factory for user role model
     *
     * @var \Magento\User\Model\RoleFactory
     */
    protected $_roleFactory;

    /**
     * User model factory
     *
     * @var \Magento\User\Model\UserFactory
     */
    protected $_userFactory;

    /**
     * Rules model factory
     *
     * @var \Magento\User\Model\RulesFactory
     */
    protected $_rulesFactory;

    /**
     * Backend auth session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\User\Model\RoleFactory $roleFactory
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\User\Model\RulesFactory $rulesFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\User\Model\RoleFactory $roleFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\User\Model\RulesFactory $rulesFactory,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_roleFactory = $roleFactory;
        $this->_userFactory = $userFactory;
        $this->_rulesFactory = $rulesFactory;
        $this->_authSession = $authSession;
    }

    /**
     * Preparing layout for output
     *
     * @return \Magento\User\Controller\Adminhtml\User\Role
     */
    protected function _initAction()
    {
        $this->_layoutServices->loadLayout();
        $this->_setActiveMenu('Magento_User::system_acl_roles');
        $this->_addBreadcrumb(__('System'), __('System'));
        $this->_addBreadcrumb(__('Permissions'), __('Permissions'));
        $this->_addBreadcrumb(__('Roles'), __('Roles'));
        return $this;
    }

    /**
     * Initialize role model by passed parameter in request
     *
     * @param string $requestVariable
     * @return \Magento\User\Model\Role
     */
    protected function _initRole($requestVariable = 'rid')
    {
        $this->_title->add(__('Roles'));

        $role = $this->_roleFactory->create()->load($this->getRequest()->getParam($requestVariable));
        // preventing edit of relation role
        if ($role->getId() && $role->getRoleType() != 'G') {
            $role->unsetData($role->getIdFieldName());
        }

        $this->_coreRegistry->register('current_role', $role);
        return $this->_coreRegistry->registry('current_role');
    }

    /**
     * Show grid with roles existing in systems
     *
     */
    public function indexAction()
    {
        $this->_title->add(__('Roles'));

        $this->_initAction();

        $this->_layoutServices->renderLayout();
    }

    /**
     * Action for ajax request from grid
     *
     */
    public function roleGridAction()
    {
        $this->_layoutServices->loadLayout(false);
        $this->_layoutServices->renderLayout();
    }

    /**
     * Edit role action
     *
     */
    public function editRoleAction()
    {
        $role = $this->_initRole();
        $this->_initAction();

        if ($role->getId()) {
            $breadCrumb      = __('Edit Role');
            $breadCrumbTitle = __('Edit Role');
        } else {
            $breadCrumb = __('Add New Role');
            $breadCrumbTitle = __('Add New Role');
        }

        $this->_title->add($role->getId() ? $role->getRoleName() : __('New Role'));

        $this->_addBreadcrumb($breadCrumb, $breadCrumbTitle);

        $this->_layoutServices->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_layoutServices->getLayout()->getBlock('adminhtml.user.role.buttons')
            ->setRoleId($role->getId())
            ->setRoleInfo($role);

        $this->_layoutServices->renderLayout();
    }

    /**
     * Remove role action
     *
     */
    public function deleteAction()
    {
        $rid = $this->getRequest()->getParam('rid', false);
        /** @var \Magento\User\Model\User $currentUser */
        $currentUser = $this->_userFactory->create()->setId($this->_authSession->getUser()->getId());

        if (in_array($rid, $currentUser->getRoles()) ) {
            $this->_session->addError(
                __('You cannot delete self-assigned roles.')
            );
            $this->_redirect('adminhtml/*/editrole', array('rid' => $rid));
            return;
        }

        try {
            $this->_initRole()->delete();

            $this->_session->addSuccess(
                __('You deleted the role.')
            );
        } catch (\Exception $e) {
            $this->_session->addError(
                __('An error occurred while deleting this role.')
            );
        }

        $this->_redirect("*/*/");
    }

    /**
     * Role form submit action to save or create new role
     *
     */
    public function saveRoleAction()
    {
        $rid        = $this->getRequest()->getParam('role_id', false);
        $resource   = $this->getRequest()->getParam('resource', false);
        $roleUsers  = $this->getRequest()->getParam('in_role_user', null);
        parse_str($roleUsers, $roleUsers);
        $roleUsers = array_keys($roleUsers);

        $oldRoleUsers = $this->getRequest()->getParam('in_role_user_old');
        parse_str($oldRoleUsers, $oldRoleUsers);
        $oldRoleUsers = array_keys($oldRoleUsers);

        $isAll = $this->getRequest()->getParam('all');
        if ($isAll) {
            $resource = array($this->_objectManager->get('Magento\Core\Model\Acl\RootResource')->getId());
        }

        $role = $this->_initRole('role_id');
        if (!$role->getId() && $rid) {
            $this->_session->addError(__('This role no longer exists.'));
            $this->_redirect('adminhtml/*/');
            return;
        }

        try {
            $roleName = $this->getRequest()->getParam('rolename', false);

            $role->setName($roleName)
                 ->setPid($this->getRequest()->getParam('parent_id', false))
                 ->setRoleType('G');
            $this->_eventManager->dispatch(
                'admin_permissions_role_prepare_save',
                array('object' => $role, 'request' => $this->getRequest())
            );
            $role->save();

            $this->_rulesFactory->create()
                ->setRoleId($role->getId())
                ->setResources($resource)
                ->saveRel();

            foreach ($oldRoleUsers as $oUid) {
                $this->_deleteUserFromRole($oUid, $role->getId());
            }

            foreach ($roleUsers as $nRuid) {
                $this->_addUserToRole($nRuid, $role->getId());
            }

            $this->_session->addSuccess(
                __('You saved the role.')
            );
        } catch (\Magento\Core\Exception $e) {
            $this->_session->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_session->addError(
                __('An error occurred while saving this role.')
            );
        }
        $this->_redirect('adminhtml/*/');
        return;
    }

    /**
     * Action for ajax request from assigned users grid
     */
    public function editrolegridAction()
    {
        $this->_layoutServices->loadLayout();
        $this->_layoutServices->renderLayout();
    }

    /**
     * Remove user from role
     *
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    protected function _deleteUserFromRole($userId, $roleId)
    {
        try {
            $this->_userFactory->create()
                ->setRoleId($roleId)
                ->setUserId($userId)
                ->deleteFromRole();
        } catch (\Exception $e) {
            throw $e;
            return false;
        }
        return true;
    }

    /**
     * Assign user to role
     *
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    protected function _addUserToRole($userId, $roleId)
    {
        $user = $this->_userFactory->create()->load($userId);
        $user->setRoleId($roleId);

        if ($user->roleUserExists() === true ) {
            return false;
        } else {
            $user->save();
            return true;
        }
    }

    /**
     * Acl checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_User::acl_roles');
    }
}
