<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Backend\Test\Repository\Urlrewrite;

use Mtf\Repository\AbstractRepository;

/**
 * Class Product
 * URL Rewrite Product Repository
 *
 * @package Magento\Backend\Test\Repository\Urlrewrite
 */
class Product extends AbstractRepository
{
    /**
     * @param array $defaultConfig
     * @param array $defaultData
     */
    public function __construct(array $defaultConfig, array $defaultData)
    {
        $this->_data['product_with_permanent_redirect'] = array(
            'config' => $defaultConfig,
            'data' => array(
                'fields' => array(
                    'request_path' => array(
                        'value' => '%rewritten_product_request_path%',
                    ),
                    'redirect' => array(
                        'value' => 'Permanent (301)',
                    ),
                ),
            ),
        );
        $this->_data['default'] = $this->_data['product_with_permanent_redirect'];
    }
}
