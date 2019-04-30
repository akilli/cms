<?php
declare(strict_types = 1);

namespace cfg;

use app;

/**
 * Loads configuration data with or without overwrites
 */
function load(string $id): array
{
    $file = app\path('cfg', $id . '.php');
    $data = is_readable($file) ? include $file : [];
    $extFile = app\path('ext.cfg', $id . '.php');

    if (!is_readable($extFile) || !($ext = include $extFile)) {
        return $data;
    }

    if (in_array($id, ['attr', 'block', 'entity'])) {
        return $data + $ext;
    }

    if ($id === 'layout') {
        return layout($data, $ext);
    }

    return array_replace_recursive($data, $ext);
}

/**
 * Load layout configuration
 */
function layout(array $data, array $ext = []): array
{
    foreach ($ext as $key => $cfg) {
        foreach ($cfg as $id => $block) {
            $data[$key][$id] = empty($data[$key][$id]) ? $block : block($data[$key][$id], $block);
        }
    }

    return $data;
}

/**
 * Load block configuration
 */
function block(array $data, array $ext = []): array
{
    if (!empty($ext['cfg'])) {
        $data['cfg'] = empty($data['cfg']) ? $ext['cfg'] : array_replace($data['cfg'], $ext['cfg']);
    }

    unset($ext['cfg']);

    return array_replace($data, $ext);
}
