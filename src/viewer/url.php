<?php
declare(strict_types=1);

namespace viewer;

use app;

/**
 * URL
 */
function url(string $val): string
{
    return app\html('a', ['href' => $val], $val);
}
