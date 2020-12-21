<?php
declare(strict_types=1);

namespace attr\browser;

use app;
use attr;

function frontend(?int $val, array $attr): string
{
    $opt = $attr['opt']();
    $html = app\html('output', ['id' => $attr['html']['id'] . '-output'], $val ? $opt[$val] : null);
    $html .= app\html('input', ['type' => 'hidden', 'value' => $val ?: ''] + $attr['html']);
    $browse = app\i18n('Browse');
    $html .= app\html(
        'a',
        ['data-id' => $attr['html']['id'], 'data-ref' => $attr['ref'], 'data-action' => 'browser', 'title' => $browse],
        $browse
    );

    if (!$attr['required']) {
        $html .= ' ';
        $remove = app\i18n('Remove');
        $html .= app\html(
            'a',
            ['data-id' => $attr['html']['id'], 'data-action' => 'remove', 'title' => $remove],
            $remove
        );
    }

    return  $html;
}
