<?php
declare(strict_types=1);

namespace attr\audio;

use html;

function viewer(string $val): string
{
    return html\element('audio', ['src' => $val, 'controls' => true]);
}
