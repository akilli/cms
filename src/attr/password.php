<?php
declare(strict_types=1);

namespace attr\password;

use html;

function frontend(?string $val, array $attr): string
{
    return html\element('input', ['type' => 'password', 'value' => false] + $attr['html']);
}
