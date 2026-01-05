<?php

declare(strict_types=1);

namespace NeutromeLabs\HeadlessComponents\Service;

use Magento\Framework\View\LayoutInterface;
use NeutromeLabs\HeadlessComponents\Block\Headless;
use NeutromeLabs\HeadlessComponents\Block\HeadlessFactory;

class Renderer
{
    private array $renderedScriptCompanions = [];

    public function __construct(
        private readonly LayoutInterface  $layout,
        private readonly HeadlessFactory  $blockFactory,
        private readonly string           $componentModule = 'NeutromeLabs_HeadlessComponents',
    ) {
    }

    public function isShortTemplate(string $template): bool
    {
        return !str_contains($template, '::');
    }

    public function createBlockInstance(array $data, ?string $slug): Headless
    {
        return $this->blockFactory
            ->create()
            ->setData(array_merge([
                'renderer' => $this,
                'slug' => $slug,
            ], $data));
    }

    private function resolveTemplate(string $template): string
    {
        if ($this->isShortTemplate($template)) {
            return "{$this->componentModule}::{$template}.phtml";
        }

        return $template;
    }

    private function renderBlock(Headless $block, string $template): string
    {
        $block->setTemplate($this->resolveTemplate($template));

        return $block->toHtml();
    }

    public function render(
        string  $template,
        array   $data = [],
        ?string $slug = null,
        string  $scriptLayoutParent = 'before.body.end'
    ): string {
        $html = '';

        // Handle script companion (e.g., atom/button.script.phtml for AlpineJS logic)
        $scriptTemplate = $this->isShortTemplate($template)
            ? "{$template}.script"
            : str_replace('.phtml', '.script.phtml', $template);

        $scriptBlock = $this->createBlockInstance($data, $slug ? "{$slug}_script" : null);

        try {
            $scriptHtml = $this->renderBlock($scriptBlock, $scriptTemplate);
            $canRenderScript = (bool) $scriptHtml;
        } catch (\Exception) {
            $canRenderScript = false;
        }

        if ($canRenderScript) {
            $scriptKey = $this->resolveTemplate($scriptTemplate);

            if (!isset($this->renderedScriptCompanions[$scriptKey])) {
                if (count($this->layout->getAllBlocks()) > 0) {
                    // Layout available: add to layout for proper placement
                    $this->layout->addBlock(
                        $scriptBlock,
                        $scriptBlock->getNameInLayout(),
                        $scriptLayoutParent
                    );
                } else {
                    // No layout (e.g., AJAX): render inline
                    $html .= $scriptHtml;
                }
                $this->renderedScriptCompanions[$scriptKey] = true;
            }
        }

        // Render main component
        $html .= $this->renderBlock($this->createBlockInstance($data, $slug), $template);

        return $html;
    }
}
