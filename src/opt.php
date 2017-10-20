<?php
declare(strict_types = 1);

namespace opt;

use attr;
use app;
use ent;

/**
 * Entity options
 */
function ent(string $eId): array
{
    if (($data = & app\data('opt.ent.' . $eId)) === null) {
        if ($eId === 'page') {
            $data = [];

            foreach (ent\all('page', [], ['select' => ['id', 'name', 'pos'], 'order' => ['pos' => 'asc']]) as $item) {
                $data[$item['id']] = attr\viewer($item, $item['_ent']['attr']['pos']) . ' ' . $item['name'];
            }
        } else {
            $data = array_column(ent\all($eId, [], ['select' => ['id', 'name']]), 'name', 'id');
        }
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
            $data[$key] = $priv['name'];
        }
    }

    return $data;
}
