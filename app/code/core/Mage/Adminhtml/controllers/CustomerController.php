<?php
/**
 * Customer admin controller
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Adminhtml_CustomerController extends Mage_Adminhtml_Controller_Action
{
    protected function _initCustomer($idFieldName = 'id')
    {
        $customerId = (int) $this->getRequest()->getParam($idFieldName);
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);
        return $this;
    }

    /**
     * Customers list action
     */
    public function indexAction()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->loadLayout('baseframe');

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('customer/manage');

        /**
         * Append customers block to content
         */
        $this->_addContent(
            $this->getLayout()->createBlock('adminhtml/customer', 'customer')
        );

        /**
         * Add breadcrumb item
         */
        $this->_addBreadcrumb(__('Customers'), __('Customers'));
        $this->_addBreadcrumb(__('Manage Customers'), __('Manage Customers'));

        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/customer_grid')->toHtml());
    }

    /**
     * Customer edit action
     */
    public function editAction()
    {
        $this->_initCustomer();
        $this->loadLayout('baseframe');

        $customer = Mage::registry('current_customer');

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getCustomerData(true);

        if (isset($data['account'])) {
            $customer->addData($data['account']);
        }
        if (isset($data['address']) && is_array($data['address'])) {
            $collection = $customer->getAddressCollection();
            foreach ($data['address'] as $addressId => $address) {
                $addressModel = Mage::getModel('customer/address')->setData($address)
                    ->setId($addressId);
            	$collection->addItem($addressModel);
            }
            $customer->setLoadedAddressCollection($collection);
        }

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('customer/new');

        /**
         * Append customer edit block to content
         */
        $this->_addContent(
            $this->getLayout()->createBlock('adminhtml/customer_edit')
        );

        /**
         * Append customer edit tabs to left block
         */
        $this->_addLeft($this->getLayout()->createBlock('adminhtml/customer_edit_tabs'));

        $this->renderLayout();
    }

    /**
     * Create new customer action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Delete customer action
     */
    public function deleteAction()
    {
        $this->_initCustomer();
        $customer = Mage::registry('current_customer');
        if ($customer->getId()) {
            try {
                $customer->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess('Customer was deleted');
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/customer');
    }

    /**
     * Save customer action
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $this->_initCustomer('customer_id');
            $customer = Mage::registry('current_customer');

            // Prepare customer saving data
            if (isset($data['account'])) {
                $customer->addData($data['account']);
            }

            if (!$customer->getId()) {
                $customer->setCreatedIn(0); // Created from admin
            }

            if (isset($data['address'])) {
                // unset template data
                if (isset($data['address']['_template_'])) {
                    unset($data['address']['_template_']);
                }

                foreach ($data['address'] as $index => $addressData) {
                    $address = Mage::getModel('customer/address');
                    $address->setData($addressData);

                    if ($addressId = (int) $index) {
                        $address->setId($addressId);
                    }
                    /**
                     * We need set post_index for detect default addresses
                     */
                    $address->setPostIndex($index);
                    $customer->addAddress($address);
                }
            }

            if(isset($data['subscription'])) {
                $customer->setIsSubscribed(true);
            } else {
                $customer->setIsSubscribed(false);
            }

            $isNewCustomer = !$customer->getId();
            try {
                # Trying to load customer with the same email and terminate saving
                # if customer with the same email address exisits
                $checkCustomer = Mage::getModel('customer/customer');
                $checkCustomer->loadByEmail($customer->getEmail());
                if( $checkCustomer->getId() ) {
                    throw new Exception(__('Customer with the same email already exisits'));
                }
                # done

                if ($customer->getPassword() == 'auto') {
                    $customer->setPassword($customer->generatePassword());
                }

                $customer->save();
                if ($isNewCustomer) {
                    $customer->sendNewAccountEmail();
                }

                if ($newPassword = $customer->getNewPassword()) {
                    if ($newPassword == 'auto') {
                        $newPassword = $customer->generatePassword();
                    }
                    $customer->changePassword($newPassword, false);
                    $customer->sendPasswordReminderEmail();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess('Customer was saved');
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCustomerData($data);
                $this->getResponse()->setRedirect(Mage::getUrl('*/customer/edit', array('id'=>$customer->getId())));
                return;
            }
        }
        $this->getResponse()->setRedirect(Mage::getUrl('*/customer'));
    }

    /**
     * Export customer grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'customers.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/customer_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Export customer grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'customers.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/customer_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content)
    {
        header('HTTP/1.1 200 OK');
        header('Content-Disposition: attachment; filename='.$fileName);
        header('Last-Modified: '.date('r'));
        header("Accept-Ranges: bytes");
        header("Content-Length: ".sizeof($content));
        header("Content-type: application/octet-stream");
        echo $content;
    }

    /**
     * Customer orders grid
     *
     */
    public function ordersAction() {
        $this->_initCustomer();
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/customer_edit_tab_orders')->toHtml());
    }

    /**
     * Customer newsletter grid
     *
     */
    public function newsletterAction()
    {
        $this->_initCustomer();
        $subscriber = Mage::getModel('newsletter/subscriber')
            ->loadByCustomer(Mage::registry('current_customer'));

        Mage::register('subscriber', $subscriber);
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/customer_edit_tab_newsletter_grid')->toHtml());
    }

    public function wishlistAction()
    {
        $this->_initCustomer();
        $customer = Mage::registry('current_customer');
        if ($customer->getId()) {
            if($itemId = (int) $this->getRequest()->getParam('delete')) {
            	try {
	            	Mage::getModel('wishlist/item')->load($itemId)
	            		->delete();
            	}
            	catch (Exception $e) {
            		//
            	}
            }
        }
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/customer_edit_tab_wishlist')->toHtml());
    }

    public function cartAction()
    {
        $this->_initCustomer();
        if ($deleteItemId = $this->getRequest()->getPost('delete')) {
            $quote = Mage::getResourceModel('sales/quote_collection')
                ->loadByCustomerId(Mage::registry('current_customer')->getId());
            $quote->removeItem($deleteItemId);
            $quote->save();
        }
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/customer_edit_tab_cart')->toHtml());
    }

    public function tagGridAction()
    {
        $this->_initCustomer();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/customer_edit_tab_tag', 'admin.customer.tags')
                ->setCustomerId(Mage::registry('current_customer'))
                ->toHtml()
        );
    }

    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(0);

        $accountData = $this->getRequest()->getPost('account');

        # Checking if we received email. If not - ERROR
        if( !($accountData['email']) ) {
            $response->setError(1);
            Mage::getSingleton('adminhtml/session')->addError(__('Please, fill in \'email\' field.'));
            $this->_initLayoutMessages('adminhtml/session');
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        } else {
            # Trying to load customer with the same email and return error message
            # if customer with the same email address exisits
            $checkCustomer = Mage::getModel('customer/customer');
            $checkCustomer->loadByEmail($accountData['email']);
            if( $checkCustomer->getId() ) {
                $response->setError(1);
                Mage::getSingleton('adminhtml/session')->addError(__('Customer with the same email already exists.'));
                $this->_initLayoutMessages('adminhtml/session');
                $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
            }
        }
        $this->getResponse()->setBody($response->toJson());
    }
}
