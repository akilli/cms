<?php
declare(strict_types=1);

namespace attr\radio;

use html;

function frontend(mixed $val, array $attr): string
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
        $html .= html\element('input', $a) . html\element('label', ['for' => $id], $v);
    }

    return $html;
}
