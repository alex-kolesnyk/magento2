<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Backend Catalog Price Rules controller
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Controller\Promo;

class Catalog extends \Magento\Adminhtml\Controller\Action
{
    /**
     * Dirty rules notice message
     *
     * @var string
     */
    protected $_dirtyRulesNoticeMessage;

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Controller\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\Controller\Context $context,
        \Magento\Core\Model\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Magento_CatalogRule::promo_catalog')
            ->_addBreadcrumb(
                __('Promotions'),
                __('Promotions')
            );
        return $this;
    }

    public function indexAction()
    {
        $this->_title(__('Catalog Price Rules'));

        $dirtyRules = \Mage::getModel('Magento\CatalogRule\Model\Flag')->loadSelf();
        if ($dirtyRules->getState()) {
            \Mage::getSingleton('Magento\Adminhtml\Model\Session')->addNotice($this->getDirtyRulesNoticeMessage());
        }

        $this->_initAction()
            ->_addBreadcrumb(
                __('Catalog'),
                __('Catalog')
            )
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_title(__('Catalog Price Rules'));

        $id = $this->getRequest()->getParam('id');
        $model = \Mage::getModel('Magento\CatalogRule\Model\Rule');

        if ($id) {
            $model->load($id);
            if (! $model->getRuleId()) {
                \Mage::getSingleton('Magento\Adminhtml\Model\Session')->addError(
                    __('This rule no longer exists.')
                );
                $this->_redirect('*/*');
                return;
            }
        }

        $this->_title($model->getRuleId() ? $model->getName() : __('New Catalog Price Rule'));

        // set entered data if was error when we do save
        $data = \Mage::getSingleton('Magento\Adminhtml\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');

        $this->_coreRegistry->register('current_promo_catalog_rule', $model);

        $this->_initAction()->getLayout()->getBlock('promo_catalog_edit')
             ->setData('action', $this->getUrl('*/promo_catalog/save'));

        $breadcrumb = $id ? __('Edit Rule') : __('New Rule');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb)->renderLayout();
    }

    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                $model = \Mage::getModel('Magento\CatalogRule\Model\Rule');
                $this->_eventManager->dispatch(
                    'adminhtml_controller_catalogrule_prepare_save',
                    array('request' => $this->getRequest())
                );
                $data = $this->getRequest()->getPost();
                $data = $this->_filterDates($data, array('from_date', 'to_date'));
                $id = $this->getRequest()->getParam('rule_id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        \Mage::throwException(__('Wrong rule specified.'));
                    }
                }

                $validateResult = $model->validateData(new \Magento\Object($data));
                if ($validateResult !== true) {
                    foreach($validateResult as $errorMessage) {
                        $this->_getSession()->addError($errorMessage);
                    }
                    $this->_getSession()->setPageData($data);
                    $this->_redirect('*/*/edit', array('id'=>$model->getId()));
                    return;
                }

                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);

                $model->loadPost($data);

                \Mage::getSingleton('Magento\Adminhtml\Model\Session')->setPageData($model->getData());

                $model->save();

                \Mage::getSingleton('Magento\Adminhtml\Model\Session')->addSuccess(
                    __('The rule has been saved.')
                );
                \Mage::getSingleton('Magento\Adminhtml\Model\Session')->setPageData(false);
                if ($this->getRequest()->getParam('auto_apply')) {
                    $this->getRequest()->setParam('rule_id', $model->getId());
                    $this->_forward('applyRules');
                } else {
                    \Mage::getModel('Magento\CatalogRule\Model\Flag')->loadSelf()
                        ->setState(1)
                        ->save();
                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', array('id' => $model->getId()));
                        return;
                    }
                    $this->_redirect('*/*/');
                }
                return;
            } catch (\Magento\Core\Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_getSession()->addError(
                    __('An error occurred while saving the rule data. Please review the log and try again.')
                );
                $this->_objectManager->get('Magento_Core_Model_Logger')->logException($e);
                \Mage::getSingleton('Magento_Adminhtml_Model_Session')->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('rule_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = \Mage::getModel('Magento\CatalogRule\Model\Rule');
                $model->load($id);
                $model->delete();
                \Mage::getModel('Magento\CatalogRule\Model\Flag')->loadSelf()
                    ->setState(1)
                    ->save();
                \Mage::getSingleton('Magento\Adminhtml\Model\Session')->addSuccess(
                    __('The rule has been deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Core\Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_getSession()->addError(
                    __('An error occurred while deleting the rule. Please review the log and try again.')
                );
                $this->_objectManager->get('Magento_Core_Model_Logger')->logException($e);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        \Mage::getSingleton('Magento\Adminhtml\Model\Session')->addError(
            __('Unable to find a rule to delete.')
        );
        $this->_redirect('*/*/');
    }

    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = \Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(\Mage::getModel('Magento\CatalogRule\Model\Rule'))
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof \Magento\Rule\Model\Condition\AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    public function chooserAction()
    {
        if ($this->getRequest()->getParam('attribute') == 'sku') {
            $type = 'Magento\Adminhtml\Block\Promo\Widget\Chooser\Sku';
        }
        if (!empty($type)) {
            $block = $this->getLayout()->createBlock($type);
            if ($block) {
                $this->getResponse()->setBody($block->toHtml());
            }
        }
    }

    public function newActionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = \Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(\Mage::getModel('Magento\CatalogRule\Model\Rule'))
            ->setPrefix('actions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof \Magento\Rule\Model\Action\AbstractAction) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * Apply all active catalog price rules
     */
    public function applyRulesAction()
    {
        $errorMessage = __('Unable to apply rules.');
        try {
            /** @var $ruleJob \Magento\CatalogRule\Model\Rule\Job */
            $ruleJob = $this->_objectManager->get('Magento\CatalogRule\Model\Rule\Job');
            $ruleJob->applyAll();

            if ($ruleJob->hasSuccess()) {
                $this->_getSession()->addSuccess($ruleJob->getSuccess());
                \Mage::getModel('Magento\CatalogRule\Model\Flag')->loadSelf()
                    ->setState(0)
                    ->save();
            } elseif ($ruleJob->hasError()) {
                $this->_getSession()->addError($errorMessage . ' ' . $ruleJob->getError());
            }
        } catch (\Exception $e) {
            $this->_getSession()->addError($errorMessage);
        }
        $this->_redirect('*/*');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_CatalogRule::promo_catalog');
    }

    /**
     * Set dirty rules notice message
     *
     * @param string $dirtyRulesNoticeMessage
     */
    public function setDirtyRulesNoticeMessage($dirtyRulesNoticeMessage)
    {
        $this->_dirtyRulesNoticeMessage = $dirtyRulesNoticeMessage;
    }

    /**
     * Get dirty rules notice message
     *
     * @return string
     */
    public function getDirtyRulesNoticeMessage()
    {
        $defaultMessage = __('There are rules that have been changed but were not applied. Please, click Apply Rules in order to see immediate effect in the catalog.');
        return $this->_dirtyRulesNoticeMessage ? $this->_dirtyRulesNoticeMessage : $defaultMessage;
    }
}
