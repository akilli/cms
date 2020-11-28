<?php
declare(strict_types=1);

namespace attr\text;

use app;
use str;

function frontend(?string $val, array $attr): string
{
    return app\html('input', ['type' => 'text', 'value' => str\enc($val)] + $attr['html']);
}

function validator(string $val): string
{
    return trim((string) filter_var($val, FILTER_SANITIZE_STRING));
}