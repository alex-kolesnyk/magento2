<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * System Configuration Save Controller
 *
 * @category   Magento
 * @package    Magento_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
namespace Magento\Backend\Controller\Adminhtml\System\Config;

class Save extends \Magento\Backend\Controller\System\AbstractConfig
{
    /**
     * Backend Config Model Factory
     *
     * @var \Magento\Backend\Model\Config\Factory
     */
    protected $_configFactory;

    /**
     * @var \Magento\Cache\FrontendInterface
     */
    protected $_cache;

    /**
     * @param \Magento\Backend\Controller\Context $context
     * @param \Magento\Backend\Model\Config\Structure $configStructure
     * @param \Magento\Backend\Model\Config\Factory $configFactory
     * @param \Magento\Cache\FrontendInterface $cache
     */
    public function __construct(
        \Magento\Backend\Controller\Context $context,
        \Magento\Backend\Model\Config\Structure $configStructure,
        \Magento\Backend\Model\Config\Factory $configFactory,
        \Magento\Cache\FrontendInterface $cache
    ) {
        parent::__construct($context, $configStructure);
        $this->_configFactory = $configFactory;
        $this->_cache = $cache;
    }

    /**
     * Save configuration
     */
    public function indexAction()
    {
        try {
            if (false == $this->_isSectionAllowed($this->getRequest()->getParam('section'))) {
                throw new \Exception(__('This section is not allowed.'));
            }

            // custom save logic
            $this->_saveSection();
            $section = $this->getRequest()->getParam('section');
            $website = $this->getRequest()->getParam('website');
            $store   = $this->getRequest()->getParam('store');

            $configData = array(
                'section' => $section,
                'website' => $website,
                'store' => $store,
                'groups' => $this->_getGroupsForSave()
            );
            /** @var \Magento\Backend\Model\Config $configModel  */
            $configModel = $this->_configFactory->create(array('data' => $configData));
            $configModel->save();

            $this->_session->addSuccess(
                __('You saved the configuration.')
            );
        } catch (\Magento\Core\Exception $e) {
            $messages = explode("\n", $e->getMessage());
            foreach ($messages as $message) {
                $this->_session->addError($message);
            }
        } catch (\Exception $e) {
            $this->_session->addException(
                $e,
                __('An error occurred while saving this configuration:') . ' ' . $e->getMessage()
            );
        }

        $this->_saveState($this->getRequest()->getPost('config_state'));
        $this->_redirect('*/system_config/edit', array('_current' => array('section', 'website', 'store')));
    }

    /**
     * Get groups for save
     *
     * @return array|null
     */
    protected function _getGroupsForSave()
    {
        $groups = $this->getRequest()->getPost('groups');
        $files = $this->getRequest()->getFiles('groups');

        if (isset($files['name']) && is_array($files['name'])) {
            /**
             * Carefully merge $_FILES and $_POST information
             * None of '+=' or 'array_merge_recursive' can do this correct
             */
            foreach ($files['name'] as $groupName => $group) {
                $data = $this->_processNestedGroups($group);
                if (!empty($data)) {
                    if (!empty($groups[$groupName])) {
                        $groups[$groupName] = array_merge_recursive((array)$groups[$groupName], $data);
                    } else {
                        $groups[$groupName] = $data;
                    }
                }
            }
        }
        return $groups;
    }

    /**
     * Process nested groups
     *
     * @param mixed $group
     * @return array
     */
    protected function _processNestedGroups($group)
    {
        $data = array();

        if (isset($group['fields']) && is_array($group['fields'])) {
            foreach ($group['fields'] as $fieldName => $field) {
                if (!empty($field['value'])) {
                    $data['fields'][$fieldName] = array('value' => $field['value']);
                }
            }
        }

        if (isset($group['groups']) && is_array($group['groups'])) {
            foreach ($group['groups'] as $groupName => $groupData) {
                $nestedGroup = $this->_processNestedGroups($groupData);
                if (!empty($nestedGroup)) {
                    $data['groups'][$groupName] = $nestedGroup;
                }
            }
        }

        return $data;
    }

    /**
     * Custom save logic for section
     */
    protected function _saveSection()
    {
        $method = '_save' . uc_words($this->getRequest()->getParam('section'), '');
        if (method_exists($this, $method)) {
            $this->$method();
        }
    }

    /**
     * Advanced save procedure
     */
    protected function _saveAdvanced()
    {
        $this->_cache->clean();
    }
}
