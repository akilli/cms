<?php
declare(strict_types=1);

namespace viewer;

use app;

/**
 * Audio
 */
function audio(string $val): string
{
    return app\html('audio', ['src' => $val, 'controls' => true]);
}
