<?php
declare(strict_types=1);

namespace attr\checkbox;

use html;

function frontend(?array $val, array $attr): string
{
    $val = (array) $val;
    $html = html\element(
        'input',
        ['id' => $attr['html']['id'], 'name' => str_replace('[]', '', $attr['html']['name']), 'type' => 'hidden']
    );

    foreach ($attr['opt']() as $k => $v) {
        $id = $attr['html']['id'] . '-' . $k;
        $a = [
            'id' => $id,
            'name' => $attr['html']['name'],
            'type' => 'checkbox',
            'value' => $k,
            'checked' => !!array_keys($val, $k, true),
        ] + $attr['html'];
        $html .= html\element('input', $a) . html\element('label', ['for' => $id], $v);
    }

    return $html;
}
