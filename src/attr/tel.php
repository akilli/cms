<?php
declare(strict_types=1);

namespace attr\tel;

use html;
use str;

function frontend(?string $val, array $attr): string
{
    return html\element('input', ['type' => 'tel', 'value' => str\enc($val)] + $attr['html']);
}

function viewer(string $val): string
{
    return html\element('a', ['href' => 'tel:' . $val], $val);
}
