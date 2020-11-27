<?php
declare(strict_types=1);

namespace frontend;

use app;

/**
 * Int
 */
function int(?int $val, array $attr): string
{
    return app\html('input', ['type' => 'number', 'value' => $val] + $attr['html'] + ['step' => '1']);
}
