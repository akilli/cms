<?php
declare(strict_types=1);

namespace attr\tel;

use app;
use str;

function frontend(?string $val, array $attr): string
{
    return app\html('input', ['type' => 'tel', 'value' => str\enc($val)] + $attr['html']);
}

function viewer(string $val): string
{
    return app\html('a', ['href' => 'tel:' . $val], $val);
}
