<?php
declare(strict_types = 1);

namespace cms;

use InvalidArgumentException;

const ALL = '_all_';
const LOG = 'cms.log';

/**
 * Runs application
 */
function app(): void
{
    $prefix = 'cms\action_';
    $act = request('action');
    $eId = request('entity');
    $entity = cfg('entity', $eId);
    $args = $entity ? [$entity] : [];

    foreach ([$prefix . $eId . '_' . $act, $prefix . $act] as $call) {
        if (is_callable($call)) {
            allowed('*/*') ? $call(...$args) : action_denied();
            return;
        }
    }

    action_error();
}

/**
 * Internal registry
 */
function & registry(string $id): ?array
{
    static $data = [];

    if (!array_key_exists($id, $data)) {
        $data[$id] = null;
    }

    return $data[$id];
}

/**
 * Gets absolute path to specified subpath in given directory
 *
 * @throws InvalidArgumentException
 */
function path(string $dir, string $id = null): string
{
    $data = & registry('path');

    if ($data === null) {
        $root = dirname(__DIR__);
        $data['cfg'] = $root .'/cfg';
        $data['data'] = '/data';
        $data['log'] = '/var/log/app';
        $data['theme'] = $root .'/www/theme';
        $data['tmp'] = '/tmp';
        $data['tpl'] = $root .'/tpl';
    }

    if (empty($data[$dir])) {
        throw new InvalidArgumentException(_('Invalid path %s', $dir));
    }

    return $data[$dir] . ($id && ($id = trim($id, '/')) ? '/' . $id : '');
}

/**
 * Config
 */
function cfg(string $section, string $id = null)
{
    $data = & registry('cfg.' . $section);

    if ($data === null) {
        $data = arr_load(path('cfg', $section . '.php'));
        $data = event('cfg.' . $section, $data);
    }

    if ($id === null) {
        return $data;
    }

    return $data[$id] ?? null;
}

/**
 * Dispatches an event
 */
function event(string $event, array $data): array
{
    if (($listeners = cfg('listener', $event)) && asort($listeners, SORT_NUMERIC)) {
        foreach (array_keys($listeners) as $call) {
            $data = $call($data);
        }
    }

    return $data;
}

/**
 * Translate
 */
function _(string $key, string ...$args): string
{
    if (!$key) {
        return '';
    }

    $key = cfg('i18n', $key) ?? $key;

    if (!$args) {
        return $key;
    }

    return vsprintf($key, $args) ?: $key;
}

/**
 * Logger
 */
function logger(string $msg): void
{
    file_put_contents(path('log', LOG), '[' . date('r') . '] ' . $msg . "\n\n", FILE_APPEND);
}

/**
 * Resolves wildcards, i.e. asterisks, for entity and action part with appropriate values from current request
 */
function resolve(string $path): string
{
    if (strpos($path, '*') === false) {
        return $path;
    }

    $parts = explode('/', $path);

    // Wildcard for Entity Part
    if ($parts[0] === '*') {
        $parts[0] = request('entity');
    }

    // Wildcard for Action Part
    if (!empty($parts[1]) && $parts[1] === '*') {
        $parts[1] = request('action');
    }

    return implode('/', $parts);
}
