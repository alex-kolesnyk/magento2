<?php
/**
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */
namespace Magento\Tools\Formatter\PrettyPrinter;

class HardLineBreak extends LineBreak {
    /**
     * This method translates this instance to a string.
     * @return string
     */
    public function __toString() {
        return "\n";
    }
}