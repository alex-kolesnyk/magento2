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
use Magento\View\Render\RenderFactory;
use Magento\View\Render\Html;

class Container implements Render
{
    /**
     * Container type
     */
    const TYPE = 'container';

    /**#@+
     * Names of container options in layout
     */
    const CONTAINER_OPT_HTML_TAG = 'htmlTag';
    const CONTAINER_OPT_HTML_CLASS = 'htmlClass';
    const CONTAINER_OPT_HTML_ID = 'htmlId';
    const CONTAINER_OPT_LABEL = 'label';
    /**#@-*/

    /**
     * @var \Magento\View\Layout\HandleFactory
     */
    protected $handleFactory;

    /**
     * @var \Magento\View\Render\RenderFactory
     */
    protected $renderFactory;

    /**
     * @param HandleFactory $handleFactory
     * @param RenderFactory $renderFactory
     */
    public function __construct(
        HandleFactory $handleFactory,
        RenderFactory $renderFactory
    ) {
        $this->handleFactory = $handleFactory;
        $this->renderFactory = $renderFactory;
    }

    /**
     * @param Element $layoutElement
     * @param Layout $layout
     * @param string $parentName
     * @return Container
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
     */
    public function register(array $element, Layout $layout, $parentName)
    {
        if (isset($element['name']) && !isset($element['is_registered'])) {
            $elementName = $element['name'];

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
     * @param $type
     * @return string
     */
    public function render(array $element, Layout $layout, $type = Html::TYPE_HTML)
    {
        $result = '';

        if (isset($element['name'])) {
            $elementName = $element['name'];
            foreach ($layout->getChildNames($elementName) as $childName => $alias) {
                $child = $layout->getElement($childName);
                /** @var $handle Render */
                $handle = $this->handleFactory->get($child['type']);
                if ($handle instanceof Render) {
                    $result .= $handle->render($child, $layout, $type);
                }
            }
        }

        $render = $this->renderFactory->get($type);

        $containerInfo['label'] = !empty($element['label']) ? $element['label'] : null;
        $containerInfo['tag'] = !empty($element['htmlTag']) ? $element['htmlTag'] : null;
        $containerInfo['class'] = !empty($element['htmlClass']) ? $element['htmlClass'] : null;
        $containerInfo['id'] = !empty($element['htmlId']) ? $element['htmlId'] : null;

        $result = $render->renderContainer($result, $containerInfo);

        return $result;
    }
}
