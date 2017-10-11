<?php
declare(strict_types = 1);

namespace asset;

use function app\_;
use arr;
use app;
use file;
use RuntimeException;

/**
 * Load entity
 */
function load(array $entity, array $crit = [], array $opts = []): array
{
    $path = app\path('data');

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
        $data = arr\filter($data, $crit);
    }

    if ($opts['order']) {
        $data = arr\order($data, $opts['order']);
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
        throw new RuntimeException(_('Could not delete %s', $data['name']));
    }
}
