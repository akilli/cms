<?php
declare(strict_types=1);

namespace filter\msg;

use app;
use html;

/**
 * Replaces message placeholder, i.e. `<msg/>`, with actual message block
 */
function filter(string $html): string
{
    $msg = '';

    foreach (app\msg() as $item) {
        $msg .= html\element('p', [], $item);
    }

    return str_replace(html\element('app-msg'), $msg ? html\element('section', ['class' => 'msg'], $msg) : '', $html);
}
