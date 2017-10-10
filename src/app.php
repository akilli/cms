<?php
declare(strict_types = 1);

namespace cms;

use InvalidArgumentException;

const ALL = '_all_';
const LOG = 'php://stdout';

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
 * Loads and returns configuration data
 */
function cfg(string $id, string $key = null)
{
    $data = & registry('cfg.' . $id);

    if ($data === null) {
        $data = arr_load(path('cfg', $id . '.php'));
        $data = event('cfg.' . $id, $data);
    }

    if ($key === null) {
        return $data;
    }

    return $data[$key] ?? null;
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
    $key = cfg('i18n', $key) ?? $key;

    return $args ? vsprintf($key, $args) : $key;
}

/**
 * Logger
 */
function logger(string $msg): void
{
    file_put_contents(LOG, '[' . date('r') . '] ' . $msg . "\n\n", FILE_APPEND);
}

/**
 * Resolves wildcards, i.e. asterisks, for entity and action part with appropriate values from current request
 */
function resolve(string $path): string
{
    return preg_replace(['#^\*/#', '#^([^/]+)/\*($|/)#'], [request('entity') . '/', '$1/' . request('action') . '$2'], $path);
}
