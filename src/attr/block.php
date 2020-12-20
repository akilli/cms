<?php
declare(strict_types=1);

namespace attr\block;

use app;
use arr;

function opt(): array
{
    $cfg = app\cfg('layout')['html'];
    unset($cfg['head']);
    $ids = array_keys(arr\filter($cfg, 'type', 'container'));

    return array_combine($ids, $ids);
}
