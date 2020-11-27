<?php
declare(strict_types=1);

namespace attr\browser;

use app;

function frontend(?int $val, array $attr): string
{
    $browse = app\i18n('Browse');
    $remove = app\i18n('Remove');
    $html = app\html('div', ['id' => $attr['html']['id'] . '-file'], $val ? $attr['viewer']($val, $attr) : '');
    $html .= app\html('input', ['type' => 'hidden', 'value' => $val ?: ''] + $attr['html']);
    $html .= app\html(
        'a',
        ['data-id' => $attr['html']['id'], 'data-ref' => $attr['ref'], 'data-action' => 'browser', 'title' => $browse],
        $browse
    );
    $html .= ' ';
    $html .= app\html('a', ['data-id' => $attr['html']['id'], 'data-action' => 'remove', 'title' => $remove], $remove);

    return  $html;
}
