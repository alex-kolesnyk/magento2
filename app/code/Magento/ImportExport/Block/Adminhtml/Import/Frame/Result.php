<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Import frame result block.
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ImportExport\Block\Adminhtml\Import\Frame;

class Result extends \Magento\Backend\Block\Template
{
    /**
     * JavaScript actions for response.
     *
     * @var array
     */
    protected $_actions = array(
        'clear'           => array(), // remove element from DOM
        'innerHTML'       => array(), // set innerHTML property (use: elementID => new content)
        'value'           => array(), // set value for form element (use: elementID => new value)
        'show'            => array(), // show specified element
        'hide'            => array(), // hide specified element
        'removeClassName' => array(), // remove specified class name from element
        'addClassName'    => array()  // add specified class name to element
    );

    /**
     * Validation messages.
     *
     * @var array
     */
    protected $_messages = array(
        'error'   => array(),
        'success' => array(),
        'notice'  => array()
    );

    /**
     * Add action for response.
     *
     * @param string $actionName
     * @param string $elementId
     * @param mixed $value OPTIONAL
     * @return \Magento\ImportExport\Block\Adminhtml\Import\Frame\Result
     */
    public function addAction($actionName, $elementId, $value = null)
    {
        if (isset($this->_actions[$actionName])) {
            if (null === $value) {
                if (is_array($elementId)) {
                    foreach ($elementId as $oneId) {
                        $this->_actions[$actionName][] = $oneId;
                    }
                } else {
                    $this->_actions[$actionName][] = $elementId;
                }
            } else {
                $this->_actions[$actionName][$elementId] = $value;
            }
        }
        return $this;
    }

    /**
     * Add error message.
     *
     * @param string $message Error message
     * @return \Magento\ImportExport\Block\Adminhtml\Import\Frame\Result
     */
    public function addError($message)
    {
        if (is_array($message)) {
            foreach ($message as $row) {
                $this->addError($row);
            }
        } else {
            $this->_messages['error'][] = $message;
        }
        return $this;
    }

    /**
     * Add notice message.
     *
     * @param mixed $message Message text
     * @param boolean $appendImportButton OPTIONAL Append import button to message?
     * @return \Magento\ImportExport\Block\Adminhtml\Import\Frame\Result
     */
    public function addNotice($message, $appendImportButton = false)
    {
        if (is_array($message)) {
            foreach ($message as $row) {
                $this->addNotice($row);
            }
        } else {
            $this->_messages['notice'][] = $message . ($appendImportButton ? $this->getImportButtonHtml() : '');
        }
        return $this;
    }

    /**
     * Add success message.
     *
     * @param mixed $message Message text
     * @param boolean $appendImportButton OPTIONAL Append import button to message?
     * @return \Magento\ImportExport\Block\Adminhtml\Import\Frame\Result
     */
    public function addSuccess($message, $appendImportButton = false)
    {
        if (is_array($message)) {
            foreach ($message as $row) {
                $this->addSuccess($row);
            }
        } else {
            $this->_messages['success'][] = $message . ($appendImportButton ? $this->getImportButtonHtml() : '');
        }
        return $this;
    }

    /**
     * Import button HTML for append to message.
     *
     * @return string
     */
    public function getImportButtonHtml()
    {
        return '&nbsp;&nbsp;<button onclick="varienImport.startImport(\'' . $this->getImportStartUrl()
            . '\', \'' . \Magento\ImportExport\Model\Import::FIELD_NAME_SOURCE_FILE . '\');" class="scalable save"'
            . ' type="button"><span><span><span>' . __('Import') . '</span></span></span></button>';
    }

    /**
     * Import start action URL.
     *
     * @return string
     */
    public function getImportStartUrl()
    {
        return $this->getUrl('adminhtml/*/start');
    }

    /**
     * Messages getter.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Messages rendered HTML getter.
     *
     * @return string
     */
    public function getMessagesHtml()
    {
        /** @var $messagesBlock \Magento\View\Block\Messages */
        $messagesBlock = $this->_layout->createBlock('Magento\View\Block\Messages');

        foreach ($this->_messages as $priority => $messages) {
            $method = "add{$priority}";

            foreach ($messages as $message) {
                $messagesBlock->$method($message);
            }
        }
        return $messagesBlock->toHtml();
    }

    /**
     * Return response as JSON.
     *
     * @return string
     */
    public function getResponseJson()
    {
        // add messages HTML if it is not already specified
        if (!isset($this->_actions['import_validation_messages'])) {
            $this->addAction('innerHTML', 'import_validation_messages', $this->getMessagesHtml());
        }
        return $this->_coreData->jsonEncode($this->_actions);
    }
}
