<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\View\Element;

use Magento\ObjectManager;
use Magento\View\Element;
use Magento\View\Render\Html;
use Magento\View\ViewFactory;
use Magento\View\Context;
use Magento\View\Render\RenderFactory;
use Magento\Core\Model\View\FileSystem;

class Template extends Base implements Element
{
    /**
     * Element type
     */
    const TYPE = 'template';

    /**
     * @var FileSystem
     */
    protected $filesystem;

    /**
     * @param Context $context
     * @param RenderFactory $renderFactory
     * @param ViewFactory $viewFactory
     * @param ObjectManager $objectManager
     * @param FileSystem $filesystem
     * @param Element $parent
     * @param array $meta
     */
    public function __construct(
        Context $context,
        RenderFactory $renderFactory,
        ViewFactory $viewFactory,
        ObjectManager $objectManager,
        FileSystem $filesystem,
        Element $parent = null,
        array $meta = array()
    ) {
        parent::__construct($context, $renderFactory, $viewFactory, $objectManager, $parent, $meta);

        $this->filesystem = $filesystem;
    }

    /**
     * @param Element $parent
     */
    public function register(Element $parent = null)
    {
        if (isset($parent)) {
            $parent->attach($this, $this->alias, $this->before, $this->after);
        }

        foreach ($this->getChildren() as $child) {
            $metaElement = $this->viewFactory->create($child['type'],
                array(
                    'context' => $this->context,
                    'parent' => $this,
                    'meta' => $child
                )
            );
            $metaElement->register($this);
        }
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function render($type = Html::TYPE_HTML)
    {
        $dataProviders = $this->getDataProviders();
        // TODO: probably prepare limited proxy to avoid violations
        $dataProviders['view'] = $this;

        $render = $this->renderFactory->get($type);
        return $render->renderTemplate($this->getTemplateFile(), $dataProviders);
    }

    /**
     * Get absolute path to template
     *
     * @return string
     */
    protected function getTemplateFile()
    {
        // TODO: rid of using area
        $this->meta['area'] = $this->context->getArea();
        $templateName = $this->filesystem->getFilename($this->meta['path'], $this->meta);
        return $templateName;
    }
}
