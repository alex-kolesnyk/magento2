<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\View\Layout\Handle\Render;

use Magento\View\Layout\Handle\AbstractHandle;
use Magento\View\LayoutInterface;
use Magento\View\Layout\Element;
use Magento\View\Layout\Handle\RenderInterface;

use Magento\View\Render\Html;

class Block extends AbstractHandle implements RenderInterface
{
    /**
     * Container type
     */
    const TYPE = 'block';

    /**
     * @param Element $layoutElement
     * @param LayoutInterface $layout
     * @param string $parentName
     * @return Block
     */
    public function parse(Element $layoutElement, LayoutInterface $layout, $parentName)
    {
        $elementName = $layoutElement->getAttribute('name');
        $elementName = $elementName ?: ('Block-' . $this->nameIncrement++);

        if (!empty($elementName)) {
            $arguments = $element = $this->parseAttributes($layoutElement);

            $element['arguments'] = $arguments;
            $element['type'] = self::TYPE;
            $element['name'] = $elementName;

            $layout->addElement($elementName, $element);

            // assign to parent element
            $this->assignToParentElement($element, $layout, $parentName);

            // parse children
            $this->parseChildren($layoutElement, $layout, $elementName);
        }

        return $this;
    }

    /**
     * @param array $element
     * @param LayoutInterface $layout
     * @param string $parentName
     * @return Block
     * @throws \InvalidArgumentException
     */
    public function register(array $element, LayoutInterface $layout, $parentName)
    {
        if (!empty($element['name']) && !isset($element['is_registered'])) {
            if (!class_exists($element['class'])) {
                throw new \InvalidArgumentException(__('Invalid block class name: ' . $element['class']));
            }

            $elementName = $element['name'];
            $arguments = isset($element['arguments']) ? $element['arguments'] : array();

            $layout->updateElement($elementName, array('is_registered' => true));

            /** @var $block \Magento\Core\Block\Template */
            $block = $layout->createBlock($element['class'], $elementName,
                array(
                    'data' => $arguments
                ));

            $block->setNameInLayout($elementName);
            $block->setLayout($layout);

            if (isset($element['template'])) {
                $block->setTemplate($element['template']);
            }

            // register children
            $this->registerChildren($elementName, $layout);
        }

        return $this;
    }

    /**
     * @param array $element
     * @param LayoutInterface $layout
     * @param string $parentName
     * @param string $type [optional]
     * @return mixed
     */
    public function render(array $element, LayoutInterface $layout, $parentName, $type = Html::TYPE_HTML)
    {
        $result = '';
        if ($block = $layout->getBlock($element['name'])) {
            $result = $block->toHtml();
        }

        return $result;
    }

    /**
     * Analyze "before" and "after" information in the node and return sibling name and whether "after" or "before"
     *
     * @param array $element
     * @return array
     */
    protected function beforeAfterToSibling($element)
    {
        $result = array(null, true);
        if (isset($element['after'])) {
            $result[0] = (string)$element['after'];
        } elseif (isset($element['before'])) {
            $result[0] = (string)$element['before'];
            $result[1] = false;
        }
        return $result;
    }
}
