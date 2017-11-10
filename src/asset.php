<?php
declare(strict_types = 1);

namespace asset;

use arr;
use app;
use file;
use RuntimeException;

/**
 * Load entity
 */
function load(array $ent, array $crit = [], array $opt = []): array
{
    $path = app\path('data');

    if (!is_dir($path)) {
        return $opt['mode'] === 'size' ? [0] : [];
    }

    $opt['order'] = $opt['mode'] === 'size' || $opt['order'] ? $opt['order'] : ['id' => 'asc'];
    $data = [];

    foreach (array_diff(scandir($path), ['.', '..']) as $id) {
        if (($file = $path . '/' . $id) && is_file($file)) {
            $data[] = ['id' => $id, 'name' => $id, 'size' => filesize($file)];
        }
    }

    if ($crit) {
        $data = arr\filter($data, $crit);
    }

    if ($opt['order']) {
        $data = arr\order($data, $opt['order']);
    }

    if ($opt['limit'] > 0) {
        $data = array_slice($data, $opt['offset'], $opt['limit'], true);
    }

    if ($opt['mode'] === 'size') {
        return [count($data)];
    }

    if ($opt['mode'] === 'one') {
        return current($data) ?: [];
    }

    return $data;
}

/**
 * Save entity
 */
function save(array $data): array
{
    $data['id'] = $data['id'] ?? $data['name'];

    return $data;
}

/**
 * Delete entity
 *
 * @throws RuntimeException
 */
function delete(array $data): void
{
    if (!file\delete(app\path('data', $data['id']))) {
        throw new RuntimeException(app\i18n('Could not delete %s', $data['name']));
    }
}
