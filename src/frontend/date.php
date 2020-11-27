<?php
declare(strict_types=1);

namespace frontend;

use app;
use attr;

/**
 * Date
 */
function date(?string $val, array $attr): string
{
    $val = $val ? attr\datetime($val, APP['date.backend'], APP['date.frontend']) : '';

    return app\html('input', ['type' => 'date', 'value' => $val] + $attr['html']);
}
