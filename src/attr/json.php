<?php
declare(strict_types=1);

namespace attr\json;

use app;
use attr\textarea;
use str;

function frontend(?array $val, array $attr): string
{
    return textarea\frontend(json_encode((array) $val), $attr);
}

function viewer(array $val): string
{
    return app\html('pre', [], str\enc(print_r($val, true)));
}
