<?php
/**
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */

namespace Magento\View\Layout;

use Magento\View\LayoutInterface;
use Magento\View\Layout\Element;

interface Handle
{
    /**
     * @param Element $layoutElement
     * @param LayoutInterface $layout
     * @param string $parentName
     */
    public function parse(Element $layoutElement, LayoutInterface $layout, $parentName);

    /**
     * @param array $element
     * @param LayoutInterface $layout
     * @param string $parentName
     * @return Handle
     */
    public function register(array $element, LayoutInterface $layout, $parentName);
}
