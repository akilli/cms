<?php
declare(strict_types=1);

namespace frontend;

use app;
use attr;

/**
 * Time
 */
function time(?string $val, array $attr): string
{
    $val = $val ? attr\datetime($val, APP['time.backend'], APP['time.frontend']) : '';

    return app\html('input', ['type' => 'time', 'value' => $val] + $attr['html']);
}
