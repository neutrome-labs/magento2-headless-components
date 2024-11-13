<?php

namespace NeutromeLabs\HeadlessComponents\Service;

use Magento\Framework\Escaper;

class AttributesHtmlGenerator
{

    public function __construct(private readonly Escaper $escaper)
    {
    }

    public function generate(?array $attributes, ?callable $callback = null): string
    {
        if (empty($attributes)) {
            return '';
        }

        $html = '';
        array_walk($attributes, function ($value, $key) use (&$html, $callback) {
            $html .= ' ' . $key . '="' . $this->escaper->escapeHtmlAttr($value) . '"';
            if ($callback) {
                $callback($key, $value);
            }
        });
        return $html;
    }
}
