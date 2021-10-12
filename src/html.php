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

function placeholder(string $html, string $tag, array $entityIds = null): array
{
    $data = [];
    $entityPattern = $entityIds ? implode('|', $entityIds) : '[a-z][a-z_\.]*';
    $pattern = sprintf('#<%1$s id="(%2$s)-(\d+)">(?:[^<]*)</%1$s>#s', $tag, $entityPattern);

    if (preg_match_all($pattern, $html, $match)) {
        foreach ($match[1] as $key => $entityId) {
            if (!in_array($match[2][$key], $data[$entityId] ?? [])) {
                $data[$entityId][] = $match[2][$key];
            }
        }
    }

    return $data;
}
