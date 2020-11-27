<?php
declare(strict_types=1);

namespace frontend;

use app;

/**
 * File
 */
function file(?string $val, array $attr): string
{
    $html = app\html('div', ['class' => 'view'], $val ? $attr['viewer']($val, $attr) : '');

    if (!$attr['required']) {
        $id = $attr['html']['id'] . '-delete';
        $del = app\html('input', ['id' => $id, 'name' => $attr['html']['name'], 'type' => 'checkbox', 'value' => '']);
        $del .= app\html('label', ['for' => $id], app\i18n('Delete'));
        $html .= app\html('div', ['class' => 'delete'], $del);
    }

    $html .= app\html('input', ['type' => 'file', 'accept' => implode(', ', $attr['accept'])] + $attr['html']);

    return $html;
}
