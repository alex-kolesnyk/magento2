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
 * Adminhtml store edit
 */

namespace Magento\Adminhtml\Block\System\Store;

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

    /**
     * Init class
     *
     */
    protected function _construct()
    {
        switch ($this->_coreRegistry->registry('store_type')) {
            case 'website':
                $this->_objectId = 'website_id';
                $saveLabel   = __('Save Web Site');
                $deleteLabel = __('Delete Web Site');
                $deleteUrl   = $this->getUrl(
                    '*/*/deleteWebsite',
                    array('item_id' => $this->_coreRegistry->registry('store_data')->getId())
                );
                break;
            case 'group':
                $this->_objectId = 'group_id';
                $saveLabel   = __('Save Store');
                $deleteLabel = __('Delete Store');
                $deleteUrl   = $this->getUrl(
                    '*/*/deleteGroup',
                    array('item_id' => $this->_coreRegistry->registry('store_data')->getId())
                );
                break;
            case 'store':
                $this->_objectId = 'store_id';
                $saveLabel   = __('Save Store View');
                $deleteLabel = __('Delete Store View');
                $deleteUrl   = $this->getUrl(
                    '*/*/deleteStore',
                    array('item_id' => $this->_coreRegistry->registry('store_data')->getId())
                );
                break;
            default:
                $saveLabel = '';
                $deleteLabel = '';
                $deleteUrl = '';
        }
        $this->_controller = 'system_store';

        parent::_construct();

        $this->_updateButton('save', 'label', $saveLabel);
        $this->_updateButton('delete', 'label', $deleteLabel);
        $this->_updateButton('delete', 'onclick', 'setLocation(\''.$deleteUrl.'\');');

        if (!$this->_coreRegistry->registry('store_data')) {
            return;
        }

        if (!$this->_coreRegistry->registry('store_data')->isCanDelete()) {
            $this->_removeButton('delete');
        }
        if ($this->_coreRegistry->registry('store_data')->isReadOnly()) {
            $this->_removeButton('save')->_removeButton('reset');
        }
    }

    /**
     * Get Header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        switch ($this->_coreRegistry->registry('store_type')) {
            case 'website':
                $editLabel = __('Edit Web Site');
                $addLabel  = __('New Web Site');
                break;
            case 'group':
                $editLabel = __('Edit Store');
                $addLabel  = __('New Store');
                break;
            case 'store':
                $editLabel = __('Edit Store View');
                $addLabel  = __('New Store View');
                break;
        }

        return $this->_coreRegistry->registry('store_action') == 'add' ? $addLabel : $editLabel;
    }

    /**
     * Build child form class form name based on value of store_type in registry
     *
     * @return string
     */
    protected function _buildFormClassName()
    {
        return parent::_buildFormClassName() . '\\' . ucwords($this->_coreRegistry->registry('store_type'));
    }
}
