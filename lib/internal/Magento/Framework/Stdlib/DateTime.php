<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib;

/**
 * Converter of date formats
 * Internal dates
 */
class DateTime
{
    /**#@+
     * Date format, used as default. Compatible with \DateTime
     */
    const DATETIME_INTERNAL_FORMAT = 'yyyy-MM-dd HH:mm:ss';

    const DATE_INTERNAL_FORMAT = 'yyyy-MM-dd';

    const DATETIME_PHP_FORMAT = 'Y-m-d H:i:s';

    const DATE_PHP_FORMAT = 'Y-m-d';

    /**#@-*/

    /**
     * Minimum allowed year value
     */
    const YEAR_MIN_VALUE = -10000;

    /**
     * Maximum allowed year value
     */
    const YEAR_MAX_VALUE = 10000;

    /**
     * Format date to internal format
     *
     * @param string|\DateTime|bool|null $date
     * @param boolean $includeTime
     * @return string|null
     */
    public function formatDate($date, $includeTime = true)
    {
        if ($date instanceof \DateTime) {
            $format = $includeTime ? self::DATETIME_PHP_FORMAT : self::DATE_PHP_FORMAT;
            return $date->format($format);
        } elseif (empty($date)) {
            return null;
        } elseif ($date === true) {
            $date = (new \DateTime())->getTimestamp();
        } elseif (!is_numeric($date)) {
            $date = (new \DateTime($date))->getTimestamp();
        }

        $format = $includeTime ? self::DATETIME_PHP_FORMAT : self::DATE_PHP_FORMAT;
        return (new \DateTime())->setTimestamp($date)->format($format);
    }

    /**
     * Check whether sql date is empty
     *
     * @param string $date
     * @return boolean
     */
    public function isEmptyDate($date)
    {
        return preg_replace('#[ 0:-]#', '', $date) === '';
    }
}
