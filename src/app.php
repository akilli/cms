<?php
declare(strict_types=1);

namespace qnd;

use InvalidArgumentException;

/**
 * Runs application
 *
 * @return void
 */
function app(): void
{
    $prefix = fqn('action_');
    $action = request('action');
    $eUid = request('entity');
    $entity = data('entity', $eUid);
    $args = $entity ? [$entity] : [];

    foreach ([$prefix . $eUid . '_' . $action, $prefix . $action] as $call) {
        if (is_callable($call)) {
            allowed() ? $call(...$args) : action_denied();
            return;
        }
    }

    action_error();
}

/**
 * Internal registry
 *
 * @param string $id
 *
 * @return array|null
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
 * @param string $dir
 * @param string $id
 *
 * @return string
 *
 * @throws InvalidArgumentException
 */
function path(string $dir, string $id = null): string
{
    $data = & registry('path');

    if ($data === null) {
        $data = [];
        $root = realpath(__DIR__ . '/..');
        $public = realpath(dirname($_SERVER['SCRIPT_FILENAME']));
        $data['asset'] = $public . '/asset';
        $data['data'] = $root . '/data';
        $data['lib'] = $public . '/lib';
        $data['log'] = $root . '/log';
        $data['template'] = $root . '/template';
        $data['theme'] = $public . '/theme';
        $data['tmp'] = sys_get_temp_dir();
    }

    if (empty($data[$dir])) {
        throw new InvalidArgumentException(_('Invalid path %s', $dir));
    }

    $id = trim($id, '/');

    return $data[$dir] . ($id ? '/' . $id : '');
}

/**
 * Dispatches an event
 *
 * @param string $event
 * @param array $data
 *
 * @return array
 */
function event(string $event, array $data): array
{
    if (($listeners = data('listener', $event)) && asort($listeners, SORT_NUMERIC)) {
        foreach (array_keys($listeners) as $call) {
            $call = fqn('listener_' . $call);
            $data = $call($data);
        }
    }

    return $data;
}

/**
 * Translate
 *
 * @param string $key
 * @param string[] ...$params
 *
 * @return string
 */
function _(string $key, string ...$params): string
{
    if (!$key) {
        return '';
    }

    $key = data('i18n.' . data('app', 'lang'), $key) ?? $key;

    if (!$params) {
        return $key;
    }

    return vsprintf($key, $params) ?: $key;
}

/**
 * Returns fully qualified name
 *
 * @param string $name
 *
 * @return string
 */
function fqn(string $name): string
{
    return strpos($name, '\\') === false ? __NAMESPACE__ . '\\' . $name : $name;
}
