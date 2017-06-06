<?php
declare(strict_types = 1);

namespace cms;

/**
 * HTML
 *
 * @param string $name
 * @param array $attrs
 * @param string $val
 * @param bool $empty
 *
 * @return string
 */
function html(string $name, array $attrs = [], string $val = null, bool $empty = false): string
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

    return '<' . $name . $a . ($empty ? ' />' : '>' . $val . '</' . $name . '>');
}
