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
 * @category    tests
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

if (!function_exists('array_replace_recursive')) {
    function array_replace_recursive()
    {
        $args = func_get_args();
        if (!isset($args[0]) || !is_array($args[0])) {
            return null;
        }
        $result = $args[0];
        for ($i = 1; $i < func_num_args(); $i++) {
            if (!is_array($args[$i])) {
                continue;
            }
            foreach ($args[$i] as $key => $value) {
                if (!isset($args[$i][$key]) || (isset($args[$i][$key]) && !is_array($args[$i][$key]))) {
                    $args[$i][$key] = array();
                }
                if (is_array($value)) {
                    $value = array_replace_recursive($args[$i][$key], $value);
                }
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
