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
 * Webservice apia2 renderer of query format model
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Renderer_Query implements Mage_Api2_Model_Renderer_Interface
{

    /**
     * Convert Array to URL-encoded query string
     *
     * @param array $data
     * @param null $options
     * @return string
     */
    public function render(array $data, $options = null)
    {
        return http_build_query($data);
    }

    /**
     * Render error content
     *
     * @param int $code
     * @param array $exceptions
     * @return string
     */
    public function renderErrors($code, $exceptions)
    {
        $domain = 'core';

        $messages = array();
        /** @var Exception $exception */
        foreach ($exceptions as $exception) {
            $message = array(
                'domain'   => $domain,
                'code'     => $exception->getCode(),
                'message'  => $exception->getMessage(),
            );
            $messages[] = $message;
        }

        return http_build_query(array('messages' => $messages));
    }
}
