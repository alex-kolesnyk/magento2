<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\View\Layout\Handle\Render;

use Magento\View\Layout;
use Magento\View\Layout\Element;
use Magento\View\Layout\Handle;
use Magento\View\Layout\Handle\Render;
use Magento\View\Layout\HandleFactory;
use Magento\View\BlockPool;

use Magento\View\Render\Html;

class Block implements Render
{
    /**
     * Container type
     */
    const TYPE = 'block';

    /**
     * @var \Magento\View\Layout\HandleFactory
     */
    protected $handleFactory;

    /**
     * @var \Magento\View\BlockPool
     */
    protected $blockPool;

    /**
     * @param HandleFactory $handleFactory
     * @param BlockPool $blockPool
     */
    public function __construct(
        HandleFactory $handleFactory,
        BlockPool $blockPool
    ) {
        $this->handleFactory = $handleFactory;
        $this->blockPool = $blockPool;
    }

    /**
     * @param Element $layoutElement
     * @param Layout $layout
     * @param string $parentName
     * @return Block
     */
    public function parse(Element $layoutElement, Layout $layout, $parentName)
    {
        $elementName = $layoutElement->getAttribute('name');
        if (isset($elementName)) {
            $element = array();
            foreach ($layoutElement->attributes() as $attributeName => $attribute) {
                if ($attribute) {
                    $element[$attributeName] = (string)$attribute;
                }
            }
            $element['type'] = self::TYPE;

            $layout->addElement($elementName, $element);

            if (isset($parentName)) {
                $alias = !empty($element['as']) ? $element['as'] : $elementName;
                $layout->setChild($parentName, $elementName, $alias);
            }

            // parse children
            if ($layoutElement->hasChildren()) {
                foreach ($layoutElement as $childXml) {
                    /** @var $childXml Element */
                    $type = $childXml->getName();
                    /** @var $handle Handle */
                    $handle = $this->handleFactory->get($type);
                    $handle->parse($childXml, $layout, $elementName);
                }
            }
        }

        return $this;
    }

    /**
     * @param array $element
     * @param Layout $layout
     * @param string $parentName
     * @throws \Exception
     */
    public function register(array $element, Layout $layout, $parentName)
    {
        if (!empty($element['name']) && !isset($element['is_registered'])) {
            if (!class_exists($element['class'])) {
                throw new \Exception(__('Invalid block class name: ' . $element['class']));
            }

            $elementName = $element['name'];
            $arguments = isset($element['arguments']) ? $element['arguments'] : array();

            /** @var $block \Magento\Core\Block\Template */
            $block = $this->blockPool->add($elementName, $element['class'], array('data' => $arguments));

            $block->setNameInLayout($elementName);
            $block->setLayout($layout);

            if (isset($element['template'])) {
                $block->setTemplate($element['template']);
            }

            $layout->setElementAttribute($elementName, 'is_registered', true);

            foreach ($layout->getChildNames($elementName) as $childName => $alias) {
                $child = $layout->getElement($childName);
                /** @var $handle Render */
                $handle = $this->handleFactory->get($child['type']);
                $handle->register($child, $layout, $elementName);
            }
        }
    }

    /**
     * @param array $element
     * @param Layout $layout
     * @param $type [optional]
     * @return mixed
     */
    public function render(array $element, Layout $layout, $type = Html::TYPE_HTML)
    {
        $result = '';
        if ($this->blockPool->get($element['name'])) {
            $result = $this->blockPool->get($element['name'])->toHtml();
        }
        return $result;
    }

}
