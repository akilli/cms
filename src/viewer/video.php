<?php
declare(strict_types=1);

namespace viewer;

use app;

/**
 * Video
 */
function video(string $val): string
{
    return app\html('video', ['src' => $val, 'controls' => true]);
}
