<?php
declare(strict_types=1);

namespace filter\msg;

use app;

/**
 * Replaces message placeholder, i.e. `<msg/>`, with actual message block
 */
function filter(string $html): string
{
    $msg = '';

    foreach (app\msg() as $item) {
        $msg .= app\html('p', [], $item);
    }

    return str_replace(app\html('app-msg'), $msg ? app\html('section', ['class' => 'msg'], $msg) : '', $html);
}
