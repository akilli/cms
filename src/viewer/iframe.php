<?php
declare(strict_types=1);

namespace viewer;

use app;

/**
 * Iframe
 */
function iframe(string $val): string
{
    return app\html('iframe', ['src' => $val, 'allowfullscreen' => 'allowfullscreen']);
}
