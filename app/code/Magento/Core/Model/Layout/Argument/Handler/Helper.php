<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Layout argument. Type helper.
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model\Layout\Argument\Handler;

class Helper extends \Magento\Core\Model\Layout\Argument\HandlerAbstract
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(\Magento\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Process argument value
     *
     * @param array $argument
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function process(array $argument)
    {
        $this->_validate($argument);
        $value = $argument['value'];

        $helper = $this->_objectManager->get($value['helperClass']);
        return call_user_func_array(array($helper, $value['helperMethod']), $value['params']);
    }

    /**
     * @param array $argument
     * @throws \InvalidArgumentException
     */
    protected function _validate(array $argument)
    {
        parent::_validate($argument);
        $value = $argument['value'];

        if (!isset($value['helperClass']) || !isset($value['helperMethod'])) {
            throw new \InvalidArgumentException(
                'Passed helper has incorrect format. ' . $this->_getArgumentInfo($argument)
            );
        }
        if (!method_exists($value['helperClass'], $value['helperMethod'])) {
            throw new \InvalidArgumentException(
                'Helper method "' . $value['helperClass'] . '::' . $value['helperMethod'] . '" does not exist.'
                . ' ' . $this->_getArgumentInfo($argument)
            );
        }
    }

    /**
     * Retrieve value from argument
     *
     * @param \Magento\Core\Model\Layout\Element $argument
     * @return array
     */
    protected function _getArgumentValue(\Magento\Core\Model\Layout\Element $argument)
    {
        $value = array(
            'helperClass' => '',
            'helperMethod' => '',
            'params' => array(),
        );

        list($value['helperClass'], $value['helperMethod']) = explode('::', $argument['helper']);

        if (isset($argument->param)) {
            foreach ($argument->param as $param) {
                $value['params'][] = (string)$param;
            }
        }
        return $value;
    }
}
