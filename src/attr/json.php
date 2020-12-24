<?php
declare(strict_types=1);

namespace attr\json;

use attr\textarea;
use html;
use str;

function frontend(?array $val, array $attr): string
{
    return textarea\frontend(json_encode((array) $val), $attr);
}

function viewer(array $val): string
{
    return html\element('pre', [], str\enc(print_r($val, true)));
}
