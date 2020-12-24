<?php
declare(strict_types=1);

namespace attr\decimal;

use html;

function frontend(?float $val, array $attr): string
{
    return html\element('input', ['type' => 'number', 'value' => $val] + $attr['html'] + ['step' => '0.01']);
}
