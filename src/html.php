<?php
declare(strict_types = 1);

namespace html;

/**
 * Generates HTML-Tag
 */
function tag(string $name, array $attrs = [], string $val = null): string
{
    $a = '';

    foreach ($attrs as $k => $v) {
        if ($v === false) {
            continue;
        } elseif ($v === true) {
            $v = $k;
        }

        $a .= ' ' . $k . '="' . addcslashes((string) $v, '"') . '"';
    }

    return '<' . $name . $a . (in_array($name, APP['html.void']) ? ' />' : '>' . $val . '</' . $name . '>');
}
