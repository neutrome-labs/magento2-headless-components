<?php

declare(strict_types=1);

namespace NeutromeLabs\HeadlessComponents\Service;

use Magento\Framework\Escaper;

class AttributesHtmlGenerator
{
    public function __construct(
        private readonly Escaper $escaper
    ) {
    }

    public function generate(?array $attributes, ?callable $callback = null): string
    {
        if (empty($attributes)) {
            return '';
        }

        $html = '';
        foreach ($attributes as $key => $value) {
            $html .= ' ' . $key . '="' . $this->escaper->escapeHtmlAttr((string) $value) . '"';
            if ($callback !== null) {
                $callback($key, $value);
            }
        }

        return $html;
    }
}
