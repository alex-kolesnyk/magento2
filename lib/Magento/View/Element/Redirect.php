<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\View\Element;

/**
 * Customer Redirect Page
 */
class Redirect extends Template
{
    /**
     *  HTML form hidden fields
     */
    protected $formFields = array();

    /**
     * @var \Magento\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Data\FormFactory $formFactory,
        array $data = array()
    ) {
        $this->formFactory = $formFactory;
        parent::__construct($context, $data);
    }

    /**
     * URL for redirect location
     *
     * @return string URL
     */
    public function getTargetURL()
    {
        return '';
    }

    /**
     * Additional custom message
     *
     * @return string Output message
     */
    public function getMessage()
    {
        return '';
    }

    /**
     * Client-side redirect engine output
     *
     * @return string
     */
    public function getRedirectOutput ()
    {
        if ($this->isHtmlFormRedirect()) {
            return $this->getHtmlFormRedirect();
        } else {
            return $this->getRedirect();
        }
    }

    /**
     * Redirect via JS location
     *
     * @return string
     */
    public function getRedirect()
    {
        return '<script type="text/javascript">
            (function($){
                $($.mage.redirect("' . $this->getTargetURL() . '"));
            })(jQuery);
        </script>';
    }

    /**
     * Redirect via HTML form submission
     *
     * @return string
     */
    public function getHtmlFormRedirect()
    {
        /** @var \Magento\Data\Form $form */
        $form = $this->formFactory->create();
        $form->setAction($this->getTargetURL())
            ->setId($this->getFormId())
            ->setName($this->getFormId())
            ->setAttr('data-auto-submit', 'true')
            ->setMethod($this->getFormMethod())
            ->setUseContainer(true);
        foreach ($this->_getFormFields() as $field => $value) {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }
        return $form->toHtml();
    }

    /**
     * HTML form or JS redirect
     *
     * @return boolean
     */
    public function isHtmlFormRedirect()
    {
        return is_array($this->_getFormFields()) && count($this->_getFormFields()) > 0;
    }

    /**
     * HTML form id/name attributes
     *
     * @return string Id/name
     */
    public function getFormId()
    {
        return '';
    }

    /**
     * HTML form method attribute
     *
     * @return string Method
     */
    public function getFormMethod()
    {
        return 'POST';
    }

    /**
     * Array of hidden form fields (name => value)
     *
     * @return array
     */
    public function getFormFields()
    {
        return array();
    }

    /**
     * Optimized getFormFields() method
     *
     * @return array
     */
    protected function _getFormFields()
    {
        if (!is_array($this->formFields) || count($this->formFields) == 0) {
            $this->formFields = $this->getFormFields();
        }
        return $this->formFields;
    }

    /**
     * Get default HTML
     *
     * @return string
     */
    protected function _getDefaultHtml()
    {
        $html = '';

        $html .= '<div class="page-title">';
        $html .= '<h1>' . __('Redirecting...') . '</h1>';
        $html .= '</div>';
        if ($this->getMessage()) {
            $html .= '<p>' . $this->getMessage() . '</p>';
        }
        $html .= $this->getRedirectOutput();
        if (!$this->isHtmlFormRedirect()) {
            $html .= '<p>' . __('Click <a href="%1">here</a> if nothing has happened', $this->getTargetURL()) . '</p>';
        }

        return $html;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getTemplate()) {
            $html = parent::_toHtml();
        } else {
            $html = $this->_getDefaultHtml();
        }
        return $html;
    }
}
