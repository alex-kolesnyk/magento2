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
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml poll manager controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Alexander Stadnitski <alexander@varien.com>
 */

class Mage_Adminhtml_PollController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('cms/poll');
        $this->_addBreadcrumb(__('Poll Manager'), __('Poll Manager'));

        $this->_addContent($this->getLayout()->createBlock('adminhtml/poll_poll'));
        $this->renderLayout();
    }

    public function editAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('cms/poll');
        $this->_addBreadcrumb(__('Poll Manager'), __('Poll Manager'), Mage::getUrl('*/*/'));
        $this->_addBreadcrumb(__('Edit Poll'), __('Edit Poll'));

        $this->getLayout()->getBlock('root')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('adminhtml/poll_edit'))
             ->_addLeft($this->getLayout()->createBlock('adminhtml/poll_edit_tabs'));

        $this->renderLayout();
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('poll/poll');
                $model->setId($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(__('Poll was deleted succesfully'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(__('Unable to find a poll to delete'));
        $this->_redirect('*/*/');
    }

    public function saveAction()
    {
        if ( $this->getRequest()->getPost() ) {
            try {
                $pollModel = Mage::getModel('poll/poll');

                if( !$this->getRequest()->getParam('id') ) {
                    $pollModel->setDatePosted(now());
                }

                if( $this->getRequest()->getParam('closed') && !$this->getRequest()->getParam('was_closed') ) {
                    $pollModel->setDateClosed(now());
                }

                if( !$this->getRequest()->getParam('closed') ) {
                    $pollModel->setDateClosed(new Zend_Db_Expr('null'));
                }

                $pollModel->setPollTitle($this->getRequest()->getParam('poll_title'))
                      ->setClosed($this->getRequest()->getParam('closed'))
                      ->setId($this->getRequest()->getParam('id'))
                      ->save();

                $answers = $this->getRequest()->getParam('answer');
                if( is_array($answers) ) {
                    foreach( $answers as $key => $answer ) {
                        $answerModel = Mage::getModel('poll/poll_answer');
                        if( intval($key) > 0 ) {
                            $answerModel->setId($key);
                        }
                        $answerModel->setAnswerTitle($answer['title'])
                            ->setVotesCount($answer['votes'])
                            ->setPollId($pollModel->getId())
                            ->save();
                    }
                }

                $answersDelete = $this->getRequest()->getParam('deleteAnswer');
                if( is_array($answersDelete) ) {
                    foreach( $answersDelete as $answer ) {
                        $answerModel = Mage::getModel('poll/poll_answer');
                        $answerModel->setId($answer)
                            ->delete();
                    }
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(__('Poll succesfully saved.'));
                Mage::getSingleton('adminhtml/session')->setPollData(false);

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPollData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    protected function _isAllowed()
    {
	    return Mage::getSingleton('admin/session')->isAllowed('cms/poll');
    }

}