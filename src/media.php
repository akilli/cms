<?php
namespace qnd;

/**
 * Load entity
 *
 * @param array $entity
 * @param array $crit
 * @param array $opts
 *
 * @return array
 */
function media_load(array $entity, array $crit = [], array $opts = []): array
{
    $path = project_path('media');

    if (!is_dir($path)) {
        return $opts['mode'] === 'size' ? [0] : [];
    }

    $opts['order'] = $opts['mode'] === 'size' || $opts['order'] ? $opts['order'] : ['id' => 'asc'];
    $format = data('format', 'datetime.backend');
    $data = [];

    foreach (array_diff(scandir($path), ['.', '..']) as $id) {
        if (($file = $path . '/' . $id) && is_file($file)) {
            $data[] = [
                'id' => $id,
                'name' => $id,
                'size' => filesize($file),
                'modified' => date($format, filemtime($file))
            ];
        }
    }

    if ($crit) {
        $data = data_filter($data, $crit, $opts);
    }

    if ($opts['order']) {
        $data = data_order($data, $opts['order']);
    }

    if ($opts['limit']) {
        $data = data_limit($data, $opts['limit'], $opts['offset']);
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
 *
 * @param array $item
 *
 * @return bool
 */
function media_save(array & $item): bool
{
    return true;
}

/**
 * Delete entity
 *
 * @param array $item
 *
 * @return bool
 */
function media_delete(array & $item): bool
{
    return true;
}
