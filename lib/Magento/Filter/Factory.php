<?php
/**
 * {license_notice}
 *
 * @category   Magento
 * @package    Magento_Filter
 * @copyright  {copyright}
 * @license    {license_link}
 */

namespace Magento\Filter;

/**
 * Magento filter factory
 */
class Factory extends FactoryAbstract
{
    /**
     * Set of filters
     *
     * @var array
     */
    protected $invokableClasses = array(
        'email'         => 'Magento\Filter\Email',
        'money'         => 'Magento\Filter\Money',
        'simple'        => 'Magento\Filter\Template\Simple',
        'object'        => 'Magento\Filter\Object',
        'sprintf'       => 'Magento\Filter\Sprintf',
        'template'      => 'Magento\Filter\Template',
        'arrayFilter'   => 'Magento\Filter\ArrayFilter',
        'removeAccents' => 'Magento\Filter\RemoveAccents'
    );

    /**
     * Shared instances, by default is shared
     *
     * @var array
     */
    protected $shared = array(
        'Magento\Filter\Sprintf' => false,
        'Magento\Filter\Money' => false,
        'Magento\Filter\RemoveAccents' => false
    );
}
