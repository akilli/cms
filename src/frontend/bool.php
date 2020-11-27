<?php
declare(strict_types=1);

namespace frontend;

use app;

/**
 * Bool
 */
function bool(?bool $val, array $attr): string
{
    $html = app\html('input', ['name' => $attr['html']['name'], 'type' => 'hidden']);

    return $html . app\html('input', ['type' => 'checkbox', 'value' => 1, 'checked' => !!$val] + $attr['html']);
}
