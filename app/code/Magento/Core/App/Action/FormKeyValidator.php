<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Core\App\Action;

class FormKeyValidator
{
    /**
     * @var \Magento\Core\Model\Session
     */
    protected $_formKey;

    /**
     * @param \Magento\Data\Form\FormKey $formKey
     */
    public function __construct(\Magento\Data\Form\FormKey $formKey)
    {
        $this->_formKey = $formKey;
    }

    /**
     * Validate form key
     *
     * @param \Magento\App\RequestInterface $request
     * @return bool
     */
    public function validate(\Magento\App\RequestInterface $request)
    {
        $formKey = $request->getParam('form_key', null);
        if (!$formKey || $formKey != $this->_formKey->getFormKey()) {
            return false;
        }
        return true;
    }
} 
