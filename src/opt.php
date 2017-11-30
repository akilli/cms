<?php
declare(strict_types = 1);

namespace opt;

use app;
use ent;

/**
 * Entity options
 */
function ent(string $eId): array
{
    if (($data = & app\data('opt.ent.' . $eId)) === null) {
        $select = !empty(app\cfg('ent', $eId)['attr']['level']) ? ['id', 'name', 'level'] : ['id', 'name'];
        $data = ent\all($eId, [], ['select' => $select]);
    }

    return $data;
}

/**
 * Privilege options
 */
function priv(): array
{
    $data = [];

    foreach (app\cfg('priv') as $key => $priv) {
        if ($priv['assignable'] && app\allowed($key)) {
            $data[$key] = $priv;
        }
    }

    return $data;
}
