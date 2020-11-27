<?php
declare(strict_types=1);

namespace viewer;

use app;
use attr;

/**
 * Datetime
 */
function datetime(string $val): string
{
    return attr\datetime($val, APP['datetime.backend'], app\cfg('app', 'datetime'));
}
