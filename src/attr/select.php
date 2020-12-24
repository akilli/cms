<?php
declare(strict_types=1);

namespace attr\select;

use app;
use html;

function frontend(mixed $val, array $attr): string
{
    $val = match (true) {
        $val === null || $val === '' => [],
        is_bool($val) => [(int) $val],
        !is_array($val) => [$val],
        default => $val,
    };
    $html = !empty($attr['html']['multiple']) ? '' : html\element('option', ['value' => ''], app\i18n('Please choose'));

    foreach ($attr['opt']() as $k => $v) {
        $html .= html\element('option', ['value' => $k, 'selected' => !!array_keys($val, $k, true)], $v);
    }

    return html\element('select', $attr['html'], $html);
}
