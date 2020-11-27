<?php
declare(strict_types=1);

namespace frontend;

use app;

/**
 * Select
 */
function select(mixed $val, array $attr): string
{
    $val = match (true) {
        $val === null || $val === '' => [],
        is_bool($val) => [(int) $val],
        !is_array($val) => [$val],
        default => $val,
    };
    $html = !empty($attr['html']['multiple']) ? '' : app\html('option', ['value' => ''], app\i18n('Please choose'));

    foreach ($attr['opt']() as $k => $v) {
        $html .= app\html('option', ['value' => $k, 'selected' => !!array_keys($val, $k, true)], $v);
    }

    return app\html('select', $attr['html'], $html);
}
