<?php

namespace NeutromeLabs\HeadlessComponents\Service;

use Magento\Framework\View\LayoutInterface;
use NeutromeLabs\HeadlessComponents\Api\ThemeInterface;
use NeutromeLabs\HeadlessComponents\Api\ThemeManagerInterface;
use NeutromeLabs\HeadlessComponents\Block\Headless;
use NeutromeLabs\HeadlessComponents\Block\HeadlessFactory;

class Renderer
{

    private array $bakedSingletonCompanions = [];

    public function __construct(
        private readonly LayoutInterface       $layout,
        private readonly HeadlessFactory       $blockFactory,
        private readonly ThemeManagerInterface $themeManager,
    )
    {
    }

    public function isShortTemplate(string $template): bool
    {
        return !str_contains($template, '::');
    }

    public function createBlockInstance(
        array   $data,
        ?string $slug
    ): Headless
    {
        return $this->blockFactory
            ->create()
            ->setData(array_merge([
                'renderer' => $this,
                'slug' => $slug,
            ], $data));
    }

    private function renderRecursive(Headless $block, ?string $template, ?ThemeInterface $theme = null): string
    {
        $theme = $theme ?? $this->themeManager->current();

        if ($template && $this->isShortTemplate($template)) {
            $fullTemplate = $theme->getModule() . "::$template.phtml";
        }

        if (isset($fullTemplate) && $fullTemplate) {
            $block->setTemplate($fullTemplate);
        } else if ($template) {
            $block->setTemplate($template);
        }

        if (!$block->getTemplateFile()) {
            if (
                isset($fullTemplate)
                && $theme->getParent()
                && ($parentTheme = $this->themeManager->find($theme->getParent()))
            ) {
                return $this->renderRecursive($block, $template, $parentTheme);
            }
        }

        return $block->toHtml();
    }

    public function render(
        string  $template,
        array   $data = [],
        ?string $slug = null,
        string $scriptLayoutParent = 'before.body.end'
    ): string
    {
        $possibleScriptCompanionTemplate = $this->isShortTemplate($template)
            ? "$template.script"
            : str_replace('.phtml', '.script.phtml', $template);

        $html = '';

        $scriptCompanionBlock = $this->createBlockInstance($data, $slug . '_script');

        // side effect: sets proper template before inserting into layout
        try {
            $canRenderCompanion = (bool)$this->renderRecursive($scriptCompanionBlock, $possibleScriptCompanionTemplate);
        } catch (\Exception $e) {
            $canRenderCompanion = false;
        }

        if ($canRenderCompanion) {
            if (count($this->layout->getAllBlocks()) > 0) {
                if (!array_key_exists($possibleScriptCompanionTemplate, $this->bakedSingletonCompanions)) {
                    $this->layout->addBlock(
                        $scriptCompanionBlock,
                        $scriptCompanionBlock->getNameInLayout(),
                        $scriptLayoutParent
                    );
                    $this->bakedSingletonCompanions[$possibleScriptCompanionTemplate] = true;
                }
            } else {
                $html .= $this->renderRecursive($scriptCompanionBlock, null);
            }
        }

        $html .= $this->renderRecursive($this->createBlockInstance($data, $slug), $template);

        return $html;
    }
}
