<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Api2
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * API2 EAV Validator
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Resource_Validator_Eav extends Mage_Api2_Model_Resource_Validator
{
    /**
     * Current form mode path
     *
     * @var string
     */
    protected $_formPath;

    /**
     * Current entity model
     *
     * @var Mage_Core_Model_Abstract
     */
    protected $_entity;

    /**
     * Current form code
     *
     * @var string
     */
    protected $_formCode;

    /**
     * Construct. Set all depends.
     *
     * @param $formPath
     * @param $entity
     * @param $formCode
     */
    public function __construct($formPath, $entity, $formCode)
    {
        // TODO: check

        $this->_formPath = $formPath;
        $this->_entity = $entity;
        $this->_formCode = $formCode;
    }

    /**
     * Validate entity.
     * If fails validation, then this method returns false, and
     * getErrors() will return an array of errors that explain why the
     * validation failed.
     *
     * @param  array $data
     * @return bool
     */
    public function isSatisfiedByData(array $data)
    {
        $errors = $this->_validate($data);
        if (true !== $errors) {
            $this->_setErrors($errors);
            return false;
        }
        return true;
    }

    /**
     * Validate entity.
     * If fails validation, then this metod return an array of errors
     * that explain why the validation failed.
     *
     * @param array $data
     * @return array|bool
     */
    protected function _validate(array $data)
    {
        /** @var $form Mage_Eav_Model_Form */
        $form = Mage::getModel($this->_formPath);
        $form->setEntity($this->_entity)
            ->setFormCode($this->_formCode)
            ->ignoreInvisible(false);

        return $form->validateData($data);
    }

    /**
     * Create validator instance for specified entity type
     *
     * @param Mage_Api2_Model_Resource $resource
     * @param string $operation
     * @return Mage_Api2_Model_Resource_Validator_Eav
     */
    public static function create(Mage_Api2_Model_Resource $resource, $operation)
    {
        /** @var $config Mage_Api2_Model_Config */
        $config = $resource->getConfig();

        $resourceType = $resource->getResourceType();
        $userType = $resource->getUserType();

        $formPath = $config->getResourceValidatorFormModel($resource->getResourceType(), self::TYPE_PERSIST,$userType);
        $formCode = $config->getResourceValidatorFormCode($resourceType, self::TYPE_PERSIST, $userType, $operation);
        $entity = Mage::getModel(
            $config->getResourceValidatorEntityModel($resourceType, self::TYPE_PERSIST, $userType)
        );

        return new self($formPath, $entity, $formCode);
    }
}
