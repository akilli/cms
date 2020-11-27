<?php
declare(strict_types=1);

namespace frontend;

use app;

/**
 * Radio
 */
function radio(mixed $val, array $attr): string
{
    $val = is_bool($val) ? (int) $val : $val;
    $html = '';

    foreach ($attr['opt']() as $k => $v) {
        $id = $attr['html']['id'] . '-' . $k;
        $a = [
            'id' => $id,
            'name' => $attr['html']['name'],
            'type' => 'radio',
            'value' => $k,
            'checked' => $k === $val,
        ] + $attr['html'];
        $html .= app\html('input', $a) . app\html('label', ['for' => $id], $v);
    }

    return $html;
}
