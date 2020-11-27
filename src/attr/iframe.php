<?php
declare(strict_types=1);

namespace attr\iframe;

use app;

function viewer(string $val): string
{
    return app\html('iframe', ['src' => $val, 'allowfullscreen' => 'allowfullscreen']);
}
