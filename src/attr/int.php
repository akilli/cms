<?php
declare(strict_types=1);

namespace attr\int;

use html;

function frontend(?int $val, array $attr): string
{
    return html\element('input', ['type' => 'number', 'value' => $val] + $attr['html'] + ['step' => '1']);
}
