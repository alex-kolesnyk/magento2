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
 * Admin poll answer edit block
 */

namespace Magento\Adminhtml\Block\Poll\Answer;

class Edit extends \Magento\Adminhtml\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($coreData, $context, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->_objectId = 'id';
        $this->_controller = 'poll_answer';
        $answerData = \Mage::getModel('Magento\Poll\Model\Poll\Answer');
        if ($this->getRequest()->getParam($this->_objectId)) {
            $answerData = \Mage::getModel('Magento\Poll\Model\Poll\Answer')
                ->load($this->getRequest()->getParam($this->_objectId));
            $this->_coreRegistry->register('answer_data', $answerData);
        }

        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/poll/edit', array(
            'id' => $answerData->getPollId(),
            'tab' => 'answers_section'
        )) . '\');');
        $this->_updateButton('save', 'label', __('Save Answer'));
        $this->_updateButton('delete', 'label', __('Delete Answer'));
    }

    public function getHeaderText()
    {
        $title = $this->escapeHtml($this->_coreRegistry->registry('answer_data')->getAnswerTitle());
        return __("Edit Answer '%1'", $title);
    }
}
