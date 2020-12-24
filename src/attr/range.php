<?php
declare(strict_types=1);

namespace attr\range;

use html;

function frontend(?int $val, array $attr): string
{
    return html\element('input', ['type' => 'range', 'value' => $val] + $attr['html'] + ['step' => '1']);
}
