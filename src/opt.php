<?php
declare(strict_types = 1);

namespace opt;

use account;
use attr;
use app;
use ent;

/**
 * Entity options
 */
function ent(string $eId): array
{
    $data = & app\data('opt.ent.' . $eId);

    if ($data === null) {
        if ($eId === 'page') {
            $data = [];

            foreach (ent\all('page', [], ['select' => ['id', 'name', 'pos'], 'order' => ['pos' => 'asc']]) as $item) {
                $data[$item['id']] = attr\viewer($item['_ent']['attr']['pos'], $item) . ' ' . $item['name'];
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
        if ($priv['active'] && !$priv['call'] && account\allowed($key)) {
            $data[$key] = $priv['name'];
        }
    }

    return $data;
}
