<?php
declare(strict_types=1);

namespace html;

/**
 * Generates an HTML-element
 */
function element(string $tag, array $attrs = [], string|int|float $val = null): string
{
    return '<' . $tag . attr($attrs) . (in_array($tag, APP['html.void']) ? '/>' : '>' . $val . '</' . $tag . '>');
}

/**
 * Generates the HTML attributes
 */
function attr(array $attrs = []): string
{
    $a = '';

    foreach ($attrs as $k => $v) {
        if ($v === false) {
            continue;
        }

        if ($v === true) {
            $v = $k;
        }

        $a .= ' ' . $k . '="' . addcslashes((string)$v, '"') . '"';
    }

    return $a;
}
