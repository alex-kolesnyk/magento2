<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Staging
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Staging Manage controller
 *
 * @category    Enterprise
 * @package     Enterprise_Staging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Adminhtml_Staging_ManageController extends Mage_Adminhtml_Controller_Action
{
    protected function _construct()
    {
        // Define module dependent translate
        $this->setUsedModuleName('Enterprise_Staging');
    }

    /**
     * Initialize staging from request parameters
     *
     * @return Enterprise_Staging_Model_Staging
     */
    protected function _initStaging($stagingId = null)
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Content Staging'))
             ->_title($this->__('Staging Websites'));

        if (is_null($stagingId)) {
            $stagingId  = (int) $this->getRequest()->getParam('id');
        }
        $staging = Mage::getModel('Enterprise_Staging_Model_Staging');

        if (!$stagingId) {
            if ($websiteId = (int) $this->getRequest()->getParam('master_website_id')) {
                $staging->setMasterWebsiteId($websiteId);
            }
            if ($type = $this->getRequest()->getParam('type')) {
                $staging->setType($type);
            }
        }
        if ($stagingId) {
            $staging->load($stagingId);
            if (!$staging->getId()) {
                return false;
            }
        }

        Mage::register('staging', $staging);
        return $staging;
    }

    /**
     * View Stagings Grid
     *
     */
    public function indexAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Content Staging'))
             ->_title($this->__('Staging Websites'));

        $this->loadLayout();
        $this->_setActiveMenu('system');
        $this->renderLayout();
    }

    /**
     * Create new staging
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Staging edit form
     */
    public function editAction()
    {
        $staging = $this->_initStaging();
        /* @var $staging Enterprise_Staging_Model_Staging */
        if (!$staging) {
            $this->_getSession()->addError(Mage::helper('Enterprise_Staging_Helper_Data')->__('Incorrect ID'));
            $this->_redirect('*/*/');
            return $this;
        }

        if ($staging->isStatusProcessing()) {
            $this->_getSession()->addNotice(Mage::helper('Enterprise_Staging_Helper_Data')
                ->__('Merge cannot be done now because a merge or rollback is in progress.')
            );
        }

        Mage::dispatchEvent('staging_edit_action', array('staging' => $staging));

        if (!$staging->getId()) {
            $defaultUnsecure= (string) Mage::getConfig()
                ->getNode('default/'.Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL);
            $defaultSecure  = (string) Mage::getConfig()
                ->getNode('default/'.Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL);
            if ($defaultSecure == '{{base_url}}' || $defaultUnsecure == '{{base_url}}') {
                $this->_getSession()->addNotice(
                    Mage::helper('Enterprise_Staging_Helper_Data')->__('Before creating a staging website, please make sure that the base URLs of the source website are properly defined.')
                );
            }

            $entryPoint = Mage::getSingleton('Enterprise_Staging_Model_Entry');
            if ($entryPoint->isAutomatic()) {
                $this->_getSession()->addNotice(
                    Mage::helper('Enterprise_Staging_Helper_Data')->__('The base URL for this website will be created automatically.')
                );
                if (!$entryPoint->canEntryPointBeCreated()) {
                    $this->_getSession()->addNotice(
                        Mage::helper('Enterprise_Staging_Helper_Data')->__('To create entry points, the folder %s must be writeable.', $entryPoint->getBaseFolder())
                    );
                }
            }
        }

        $this->_title($staging->getId() ? $staging->getName() : $this->__('New Staging'));

        $this->loadLayout();
        $this->_setActiveMenu('system');
        $this->renderLayout();
    }

    /**
     * Retrieve Staging Grid HTML content for AJAX request
     */
    public function gridAction()
    {
        $staging = $this->_initStaging();

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Validate Staging before it save
     *
     */
    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);

        try {
            $stagingData = $this->getRequest()->getPost('staging');
            Mage::getModel('Enterprise_Staging_Model_Staging')
                ->setStagingId($this->getRequest()->getParam('id'))
                ->addData($stagingData)
                ->validate();
        } catch (Enterprise_Staging_Exception $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('Enterprise_Staging_Helper_Data')->__('An error occurred while validating data. Please review the log and try again.')
            );
            $this->_initLayoutMessages('Mage_Adminhtml_Model_Session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Initialize staging before saving
     *
     * @return Enterprise_Staging_Model_Staging
     */
    protected function _initStagingSave()
    {
        $staging = $this->_initStaging();
        if (!$staging) {
            return false;
        }

        $stagingData = $this->getRequest()->getPost('staging');
        if (is_array($stagingData)) {
            $staging->addData($stagingData);
        }
        return $staging;
    }

    /**
     * Save/Create Staging action
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('staging');
        $redirectBack = $this->getRequest()->getParam('back', false);
        if ($data) {
            $staging    = $this->_initStagingSave();
            if (!$staging) {
                $this->_getSession()->addError(Mage::helper('Enterprise_Staging_Helper_Data')->__('Incorrect ID'));
                $this->_redirect('*/*/', array('_current' => true));
                return $this;
            }
            $isNew = !$staging->getId();

            if ($isNew) {
                if (!$staging->checkCoreFlag()) {
                    $this->_getSession()->addError(
                        Mage::helper('Enterprise_Staging_Helper_Data')->__('Cannot perform the create operation because reindexing process or another staging operation is running.')
                    );
                    $this->_redirect('*/*/edit', array(
                        '_current'  => true
                    ));
                    return $this;
                }
            }
            try {
                $entryPoint = Mage::getSingleton('Enterprise_Staging_Model_Entry');
                if ($entryPoint->isAutomatic()) {
                    if (!$entryPoint->canEntryPointBeCreated()) {
                        $redirectBack = true;
                        Mage::throwException(
                            Mage::helper('Enterprise_Staging_Helper_Data')->__('Please make sure that folder %s exists and is writeable.', $entryPoint->getBaseFolder())
                        );
                    }
                }

                $staging->getMapperInstance()->setCreateMapData($data);
                $staging->setIsNew($isNew);
                $staging->save();

                if ($isNew) {
                    $this->_getSession()->addSuccess(
                        Mage::helper('Enterprise_Staging_Helper_Data')->__('The staging website has been created.')
                    );
                } else {
                    $this->_getSession()->addSuccess(
                        Mage::helper('Enterprise_Staging_Helper_Data')->__('The staging website has been saved.')
                    );
                }
                Mage::dispatchEvent('on_enterprise_staging_save', array('staging' => $staging));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $staging->releaseCoreFlag();
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('Enterprise_Staging_Helper_Data')->__('An error occurred while saving staging data. Please review log and try again.')
                );
                Mage::logException($e);
                $staging->releaseCoreFlag();
            }
        }

        if ($redirectBack) {
            $this->_redirect('*/*/edit', array('_current' => true));
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Reset Staging Status to allow next merge
     * needs if previous create/merge/rollback process was not fully finished
     *
     */
    public function resetStatusAction()
    {
        $staging = $this->_initStaging();
        /* @var $staging Enterprise_Staging_Model_Staging */
        if (!$staging) {
            $this->_getSession()->addError(Mage::helper('Enterprise_Staging_Helper_Data')->__('Incorrect ID'));
            $this->_redirect('*/*/');
            return $this;
        }

        $redirectBack = false;

        try {
            $staging->reset();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
            return $this;
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
            return $this;
        }

        $this->_redirect('*/*/edit', array(
            'id' => $staging->getId()
        ));
    }

    /**
     * Staging merge view action
     *
     */
    public function mergeAction()
    {
        $staging = $this->_initStaging();
        /* @var $staging Enterprise_Staging_Model_Staging */
        if (!$staging) {
            $this->_getSession()->addError(Mage::helper('Enterprise_Staging_Helper_Data')->__('Incorrect ID'));
            $this->_redirect('*/*/');
            return $this;
        }

        if (!$staging->canMerge()) {
            $this->_getSession()->addError(
                Mage::helper('Enterprise_Staging_Helper_Data')->__('The staging Website "%s" cannot be merged at this moment.', $staging->getName())
            );
            $this->_redirect('*/*/');
            return $this;
        }

        $this->_title($this->__('Merging'))->_title($staging->getName());

        $this->_getSession()->addNotice(
            Mage::helper('Enterprise_Staging_Helper_Data')->__('If no store view mapping is specified, only website-related information will be merged.')
        );

        $this->loadLayout();
        $this->_setActiveMenu('system');
        $this->renderLayout();
    }

    /**
     * Staging Merge action
     *
     */
    public function mergePostAction()
    {
        $staging = $this->_initStaging();
        /* @var $staging Enterprise_Staging_Model_Staging */
        if (!$staging) {
            $this->_getSession()->addError(Mage::helper('Enterprise_Staging_Helper_Data')->__('Incorrect ID'));
            $this->_redirect('*/*/');
            return $this;
        }

        $stagingId      = $staging->getId();

        $redirectBack   = $this->getRequest()->getParam('back', false);
        $isMergeLater   = $this->getRequest()->getPost('schedule_merge_later_flag');
        $schedulingDate = $this->getRequest()->getPost('schedule_merge_later');
        $mapData        = $this->getRequest()->getPost('map');

        if (!$staging->checkCoreFlag()) {
            $this->_getSession()->addError(
                Mage::helper('Enterprise_Staging_Helper_Data')->__('Cannot perform the merge operation because reindexing process or another staging operation is running.')
            );
            $this->_redirect('*/*/edit', array(
                '_current'  => true
            ));
            return $this;
        }

        if (!empty($mapData)) {
            try {
                $staging->getMapperInstance()->setMergeMapData($mapData);

                //scheduling merge
                if ($isMergeLater && !empty($schedulingDate)) {
                    $staging->setIsMergeLater(true);

                    //convert to internal time
                    $date = Mage::getModel('Mage_Core_Model_Date')->gmtDate(null, $schedulingDate);
                    $staging->setMergeSchedulingDate($date);

                    $originDate = Mage::helper('core')->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM,
                        true);
                    $staging->setMergeSchedulingOriginDate($originDate);

                    $staging->setMergeSchedulingMap(
                        $staging->getMapperInstance()->serialize()
                    );

                } else {
                    if (!empty($mapData['backup'])) {
                        // run create database backup
                        $staging->backup();
                    }
                }

                $staging->merge();

                $staging->setDontRunStagingProccess(true)
                    ->save();

                if ($isMergeLater && !empty($schedulingDate)) {
                    $this->_getSession()->addSuccess(
                        Mage::helper('Enterprise_Staging_Helper_Data')->__('The staging website has been scheduled to merge.')
                    );
                } else {
                    $this->_getSession()->addSuccess(
                        Mage::helper('Enterprise_Staging_Helper_Data')->__('The staging website has been merged.')
                    );
                }
                Mage::dispatchEvent('on_enterprise_staging_merge', array('staging' => $staging));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $staging->releaseCoreFlag();
                $redirectBack = true;
            } catch (Exception $e) {
                $this->_getSession()->addException(
                    $e,
                    Mage::helper('Enterprise_Staging_Helper_Data')->__('An error occurred while merging. Please review log and try again.')
                );
                $staging->releaseCoreFlag();
                $redirectBack = true;
            }
        }

        if ($redirectBack) {
            $this->_redirect('*/*/merge', array(
                'id'        => $stagingId,
                '_current'  => true
            ));
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Unscheduling merge action
     */
    public function unscheduleAction()
    {
        $staging = $this->_initStaging();

        if (!$staging) {
            $this->_getSession()->addError(Mage::helper('Enterprise_Staging_Helper_Data')->__('Incorrect ID'));
            $this->_redirect('*/*/');
            return $this;
        }

        try {
            $staging->unscheduleMege();
            $this->_getSession()->addSuccess(Mage::helper('Enterprise_Staging_Helper_Data')->__('Staging has been unscheduled.'));
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('Enterprise_Staging_Helper_Data')->__('Failed to unschedule merge.'));
        }

        $this->_redirect('*/*/');
    }

    /**
     * Check current user permission on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed('system/enterprise_staging/staging_grid');
    }
}
