<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_VersionsCms
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Mian tab with cms page attributes and some modifications to CE version
 *
 * @category    Magento
 * @package     Magento_VersionsCms
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\VersionsCms\Block\Adminhtml\Cms\Page\Revision\Edit\Tab;

class Content
    extends \Magento\Adminhtml\Block\Cms\Page\Edit\Tab\Content
{
    /**
     * Cms data
     *
     * @var \Magento\VersionsCms\Helper\Data
     */
    protected $_cmsData = null;

    /**
     * @param \Magento\VersionsCms\Helper\Data $cmsData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Data\Form\Factory $formFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Model\Event\Manager $eventManager
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\VersionsCms\Helper\Data $cmsData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Data\Form\Factory $formFactory,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Model\Event\Manager $eventManager,
        \Magento\Core\Model\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_cmsData = $cmsData;
        parent::__construct(
            $context, $formFactory, $coreData, $eventManager, $coreRegistry, $data
        );
    }

    /**
     * Preparing form by adding extra fields.
     * Adding on change js call.
     *
     * @return \Magento\VersionsCms\Block\Adminhtml\Cms\Page\Revision\Edit\Tab\Content
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('cms_page');

        parent::_prepareForm();

        $this->_cmsData->addOnChangeToFormElements($this->getForm(), 'dataChanged();');

        /* @var $fieldset \Magento\Data\Form\Element\Fieldset */
        $fieldset = $this->getForm()->getElement('content_fieldset');

        if ($model->getPageId()) {
            $fieldset->addField('page_id', 'hidden', array(
                'name' => 'page_id',
            ));

            $fieldset->addField('version_id', 'hidden', array(
                'name' => 'version_id',
            ));

            $fieldset->addField('revision_id', 'hidden', array(
                'name' => 'revision_id',
            ));

            $fieldset->addField('label', 'hidden', array(
                'name' => 'label',
            ));

            $fieldset->addField('user_id', 'hidden', array(
                'name' => 'user_id',
            ));
        }

        $this->getForm()->setValues($model->getData());

        // setting current user id for new version functionality.
        // in posted data there will be current user
        $this->getForm()->getElement('user_id')->setValue(\Mage::getSingleton('Magento\Backend\Model\Auth\Session')->getUser()->getId());

        return $this;
    }

    /**
     * Check permission for passed action
     * Rewrite CE save permission to EE save_revision
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        if ($action == 'Magento_Cms::save') {
            $action = 'Magento_VersionsCms::save_revision';
        }
        return parent::_isAllowedAction($action);
    }
}
