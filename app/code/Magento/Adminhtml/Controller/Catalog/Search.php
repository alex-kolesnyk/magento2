<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */


class Magento_Adminhtml_Controller_Catalog_Search extends Magento_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Magento_CatalogSearch::catalog_search')
            ->_addBreadcrumb(__('Search'), __('Search'))
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_title(__('Search Terms'));

        $this->_initAction()
            ->_addBreadcrumb(__('Catalog'), __('Catalog'));
            $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_title(__('Search Terms'));

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('Magento_CatalogSearch_Model_Query');

        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('Magento_Adminhtml_Model_Session')->addError(__('This search no longer exists.'));
                $this->_redirect('*/*');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = Mage::getSingleton('Magento_Adminhtml_Model_Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        Mage::register('current_catalog_search', $model);

        $this->_initAction();

        $this->_title($id ? $model->getQueryText() : __('New Search'));

        $this->getLayout()->getBlock('head')->setCanLoadRulesJs(true);

        $this->getLayout()->getBlock('adminhtml.catalog.search.edit')
            ->setData('action', $this->getUrl('*/catalog_search/save'));

        $this
            ->_addBreadcrumb($id ? __('Edit Search') : __('New Search'), $id ? __('Edit Search') : __('New Search'));

        $this->renderLayout();
    }

    /**
     * Save search query
     *
     */
    public function saveAction()
    {
        $hasError   = false;
        $data       = $this->getRequest()->getPost();
        $queryId    = $this->getRequest()->getPost('query_id', null);
        if ($this->getRequest()->isPost() && $data) {
            /* @var $model Magento_CatalogSearch_Model_Query */
            $model = Mage::getModel('Magento_CatalogSearch_Model_Query');

            // validate query
            $queryText  = $this->getRequest()->getPost('query_text', false);
            $storeId    = $this->getRequest()->getPost('store_id', false);

            try {
                if ($queryText) {
                    $model->setStoreId($storeId);
                    $model->loadByQueryText($queryText);
                    if ($model->getId() && $model->getId() != $queryId) {
                        Mage::throwException(
                            __('You already have an identical search term query.')
                        );
                    } else if (!$model->getId() && $queryId) {
                        $model->load($queryId);
                    }
                } else if ($queryId) {
                    $model->load($queryId);
                }

                $model->addData($data);
                $model->setIsProcessed(0);
                $model->save();

            } catch (Magento_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $hasError = true;
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    __('Something went wrong while saving the search query.')
                );
                $hasError = true;
            }
        }

        if ($hasError) {
            $this->_getSession()->setPageData($data);
            $this->_redirect('*/*/edit', array('id' => $queryId));
        } else {
            $this->_redirect('*/*');
        }
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('Magento_CatalogSearch_Model_Query');
                $model->setId($id);
                $model->delete();
                Mage::getSingleton('Magento_Adminhtml_Model_Session')->addSuccess(__('You deleted the search.'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('Magento_Adminhtml_Model_Session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('Magento_Adminhtml_Model_Session')->addError(__('We can\'t find a search term to delete.'));
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $searchIds = $this->getRequest()->getParam('search');
        if(!is_array($searchIds)) {
             Mage::getSingleton('Magento_Adminhtml_Model_Session')->addError(__('Please select catalog searches.'));
        } else {
            try {
                foreach ($searchIds as $searchId) {
                    $model = Mage::getModel('Magento_CatalogSearch_Model_Query')->load($searchId);
                    $model->delete();
                }
                Mage::getSingleton('Magento_Adminhtml_Model_Session')->addSuccess(
                    __('Total of %1 record(s) were deleted', count($searchIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('Magento_Adminhtml_Model_Session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_CatalogSearch::search');
    }
}
