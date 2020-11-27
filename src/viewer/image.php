<?php
declare(strict_types=1);

namespace viewer;

use app;

/**
 * Image
 */
function image(string $val): string
{
    return app\html('img', ['src' => $val]);
}
