<?php
/**
 * Customer Service Address Interface
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\TestModule4\Service\Entity\V1;


class DtoRequest extends \Magento\Service\Entity\AbstractDto
{
    /**
     * @return string
     */
    public function getName()
    {
        return $this->_get('name');
    }

    /**
     * @param string $name
     *
     * @return DtoRequest
     */
    public function setName($name)
    {
        return $this->_set('name', $name);
    }

    /**
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->_get('entity_id');
    }

    /**
     * @param int $entityId
     *
     * @return DtoRequest
     */
    public function setEntityId($entityId)
    {
        return $this->_set('entity_id', $entityId);
    }

}