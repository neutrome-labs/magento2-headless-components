<?php

namespace NeutromeLabs\HeadlessComponents\Block;

use Magento\Framework\View\Element\Template;
use NeutromeLabs\HeadlessComponents\Service\AttributesHtmlGenerator;
use NeutromeLabs\HeadlessComponents\Service\Renderer;

class Headless extends Template
{

    public function __construct(
        public readonly AttributesHtmlGenerator $attributesHtmlGenerator,
        public readonly Template\Context        $context,
        array                                   $data = []
    )
    {
        parent::__construct($context, $data);
    }

    public function getRenderer(): Renderer
    {
        return $this->getData('renderer');
    }

    public function getSlug(): string
    {
        if (!$this->getData('slug')) {
            $this->setData('slug', uniqid());
        }

        return $this->getData('slug');
    }

    public function getNameInLayout()
    {
        return parent::getNameInLayout() ?? ("headless." . $this->getSlug());
    }

    public function getCacheKeyInfo()
    {
        $info = parent::getCacheKeyInfo();
        $info['headless'] = $this->getNameInLayout();
        return $info;
    }
}
