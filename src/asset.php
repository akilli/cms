<?php
declare(strict_types = 1);

namespace cms;

use RuntimeException;

/**
 * Load entity
 */
function asset_load(array $entity, array $crit = [], array $opts = []): array
{
    $path = path('data');

    if (!is_dir($path)) {
        return $opts['mode'] === 'size' ? [0] : [];
    }

    $opts['order'] = $opts['mode'] === 'size' || $opts['order'] ? $opts['order'] : ['id' => 'asc'];
    $data = [];

    foreach (array_diff(scandir($path), ['.', '..']) as $id) {
        if (($file = $path . '/' . $id) && is_file($file)) {
            $data[] = ['id' => $id, 'name' => $id, 'size' => filesize($file), 'file' => $file];
        }
    }

    if ($crit) {
        $data = arr_filter($data, $crit);
    }

    if ($opts['order']) {
        $data = arr_order($data, $opts['order']);
    }

    if ($opts['limit'] > 0) {
        $data = array_slice($data, $opts['offset'], $opts['limit'], true);
    }

    if ($opts['mode'] === 'size') {
        return [count($data)];
    }

    if ($opts['mode'] === 'one') {
        return current($data) ?: [];
    }

    return $data;
}

/**
 * Save entity
 */
function asset_save(array $data): array
{
    $data['id'] = $data['id'] ?? $data['name'];

    return $data;
}

/**
 * Delete entity
 *
 * @throws RuntimeException
 */
function asset_delete(array $data): void
{
    if (!file_delete(path('data', $data['id']))) {
        throw new RuntimeException(_('Could not delete %s', $data['name']));
    }
}
