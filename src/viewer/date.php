<?php
declare(strict_types=1);

namespace viewer;

use app;
use attr;

/**
 * Date
 */
function date(string $val): string
{
    return attr\datetime($val, APP['date.backend'], app\cfg('app', 'date'));
}
