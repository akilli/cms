<?php
declare(strict_types=1);

namespace attr\iframe;

use html;

function viewer(string $val): string
{
    return html\element('iframe', ['src' => $val, 'allowfullscreen' => 'allowfullscreen']);
}
