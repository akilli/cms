<?php
declare(strict_types=1);

namespace attr\audio;

use app;

function viewer(string $val): string
{
    return app\html('audio', ['src' => $val, 'controls' => true]);
}
