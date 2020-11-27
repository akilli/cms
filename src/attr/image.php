<?php
declare(strict_types=1);

namespace attr\image;

use app;

function viewer(string $val): string
{
    return app\html('img', ['src' => $val]);
}
