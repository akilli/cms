<?php
declare(strict_types=1);

namespace viewer;

use app;
use str;

/**
 * JSON
 */
function json(array $val): string
{
    return app\html('pre', [], str\enc(print_r($val, true)));
}
