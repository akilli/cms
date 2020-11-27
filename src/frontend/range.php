<?php
declare(strict_types=1);

namespace frontend;

use app;

/**
 * Range
 */
function range(?int $val, array $attr): string
{
    return app\html('input', ['type' => 'range', 'value' => $val] + $attr['html'] + ['step' => '1']);
}
