<?php
declare(strict_types=1);

namespace attr\video;

use html;

function viewer(string $val): string
{
    return html\element('video', ['src' => $val, 'controls' => true]);
}
