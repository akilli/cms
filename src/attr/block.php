<?php
declare(strict_types=1);

namespace attr\block;

use app;
use arr;

function opt(): array
{
    if (($opt = &app\registry('opt')['block']) === null) {
        $cfg = app\cfg('layout')['html'];
        unset($cfg['head']);
        $ids = array_keys(arr\filter($cfg, 'type', 'container'));
        $opt = array_combine($ids, $ids);
    }

    return $opt;
}
