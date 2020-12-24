<?php
declare(strict_types=1);

namespace attr\image;

use html;

function viewer(string $val): string
{
    return html\element('img', ['src' => $val]);
}
