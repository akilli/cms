<?php
declare(strict_types=1);

namespace opt;

use app;
use arr;

/**
 * Block
 */
function block(): array
{
    $cfg = app\cfg('layout')['_all_'];
    unset($cfg['head']);
    $ids = array_keys(arr\filter($cfg, 'type', 'container'));

    return array_combine($ids, $ids);
}
