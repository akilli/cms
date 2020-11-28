<?php
declare(strict_types=1);

namespace contentfilter\msg;

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

    return str_replace(
        app\html('msg'),
        $msg ? app\html('section', ['class' => 'msg'], $msg) : '',
        $html
    );
}
