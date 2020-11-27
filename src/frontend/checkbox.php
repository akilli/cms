<?php
declare(strict_types=1);

namespace frontend;

use app;

/**
 * Checkbox
 */
function checkbox(?array $val, array $attr): string
{
    $val = (array) $val;
    $html = app\html(
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
        $html .= app\html('input', $a) . app\html('label', ['for' => $id], $v);
    }

    return $html;
}
