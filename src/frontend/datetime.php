<?php
declare(strict_types=1);

namespace frontend;

use app;
use attr;

/**
 * Datetime
 */
function datetime(?string $val, array $attr): string
{
    $val = $val ? attr\datetime($val, APP['datetime.backend'], APP['datetime.frontend']) : '';

    return app\html('input', ['type' => 'datetime-local', 'value' => $val] + $attr['html']);
}
