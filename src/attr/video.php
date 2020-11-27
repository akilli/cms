<?php
declare(strict_types=1);

namespace attr\video;

use app;

function viewer(string $val): string
{
    return app\html('video', ['src' => $val, 'controls' => true]);
}
