<?php
declare(strict_types = 1);

namespace memory;

use app;
use arr;

/**
 * Load entity
 */
function load(array $ent, array $crit = [], array $opt = []): array
{
    $db = app\data('memory.' . $ent['id']);

    if (!$db || $crit && !($db = arr\crit($db, $crit))) {
        return $opt['mode'] === 'size' ? [0] : [];
    }

    if ($opt['mode'] === 'size') {
        return [count($db)];
    }

    if ($opt['order']) {
        $db = arr\order($db, $opt['order']);
    }

    if ($opt['limit'] > 0) {
        $db = array_slice($db, $opt['offset'], $opt['limit'], true);
    }

    if ($opt['mode'] === 'one') {
        return current($db) ?: [];
    }

    return $db;
}

/**
 * Save entity
 */
function save(array $data): array
{
    $db = & app\data('memory.' . $data['_ent']['id']);

    if (!$data['_old'] && $data['_ent']['attr']['id']['type'] === 'serial') {
        $data['id'] = max(array_keys($db)) + 1;
    }

    $db[$data['id']] = $data;

    return $data;
}

/**
 * Delete entity
 */
function delete(array $data): void
{
    $db = & app\data('memory.' . $data['_ent']['id']);
    unset($db[$data['id']]);
}
