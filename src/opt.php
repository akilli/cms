<?php
declare(strict_types = 1);

namespace opt;

use account;
use attr;
use app;
use entity;

/**
 * Entity options
 */
function entity(string $eId): array
{
    $data = & app\data('opt.entity.' . $eId);

    if ($data === null) {
        if ($eId === 'page') {
            $data = [];

            foreach (entity\all('page', [], ['select' => ['id', 'name', 'pos'], 'order' => ['pos' => 'asc']]) as $item) {
                $data[$item['id']] = attr\viewer($item['_entity']['attr']['pos'], $item) . ' ' . $item['name'];
            }
        } else {
            $data = array_column(entity\all($eId, [], ['select' => ['id', 'name']]), 'name', 'id');
        }
    }

    return $data;
}

/**
 * Privilege options
 */
function privilege(): array
{
    $data = [];

    foreach (app\cfg('privilege') as $key => $priv) {
        if (empty($priv['call']) && account\allowed($key)) {
            $data[$key] = $priv['name'];
        }
    }

    return $data;
}
