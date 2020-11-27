<?php
declare(strict_types=1);

namespace viewer;

use app;
use attr;

/**
 * Time
 */
function time(string $val): string
{
    return attr\datetime($val, APP['time.backend'], app\cfg('app', 'time'));
}
