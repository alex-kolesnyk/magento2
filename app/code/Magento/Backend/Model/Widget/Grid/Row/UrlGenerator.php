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
 * Grid row url generator
 */
namespace Magento\Backend\Model\Widget\Grid\Row;

class UrlGenerator
    implements \Magento\Backend\Model\Widget\Grid\Row\GeneratorInterface
{
    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_urlModel;

    /**
     * @var string
     */
    protected $_path;

    /**
     * @var array
     */
    protected $_params = array();

    /**
     * @var array
     */
    protected $_extraParamsTemplate = array();

    /**
     * @param \Magento\Backend\Model\UrlProxy $backendUrl
     * @param array $args
     * @throws \InvalidArgumentException
     */
    public function __construct(\Magento\Backend\Model\UrlProxy $backendUrl, array $args = array())
    {
        if (!isset($args['path'])) {
            throw new \InvalidArgumentException('Not all required parameters passed');
        }
        $this->_urlModel = isset($args['urlModel']) ? $args['urlModel'] : $backendUrl;
        $this->_path = (string) $args['path'];
        if (isset($args['params'])) {
            $this->_params = (array) $args['params'];
        }
        if (isset($args['extraParamsTemplate'])) {
            $this->_extraParamsTemplate = (array) $args['extraParamsTemplate'];
        }
    }

    /**
     * Create url for passed item using passed url model
     * @param \Magento\Object $item
     * @return string
     */
    public function getUrl($item)
    {
        if (!empty($this->_path)) {
            $params = $this->_prepareParameters($item);
            return $this->_urlModel->getUrl($this->_path, $params);
        }
        return '';
    }

    /**
     * Convert template params array and merge with preselected params
     * @param $item
     * @return mixed
     */
    protected function _prepareParameters($item)
    {
        $params = array();
        foreach ($this->_extraParamsTemplate as $paramKey => $paramValueMethod) {
            $params[$paramKey] = $item->$paramValueMethod();
        }
        return array_merge($this->_params, $params);
    }
}
