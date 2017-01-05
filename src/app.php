<?php
namespace qnd;

use InvalidArgumentException;

/**
 * Constants
 */
const BACKEND_DATE = 'Y-m-d';
const BACKEND_DATETIME = 'Y-m-d H:i:s';
const BACKEND_TIME = 'H:i:s';
const FRONTEND_DATE = 'Y-m-d';
const FRONTEND_DATETIME = 'Y-m-d\TH:i';
const FRONTEND_TIME = 'H:i';
const PRIVILEGE = '_all_';
const PROJECT = 'base';
const THEME = 'base';

/**
 * Runs application
 *
 * @return void
 */
function app(): void
{
    // Reset registry
    registry();

    // Dispatch request
    $prefix = fqn('action_');
    $action = request('action');
    $eId = request('entity');
    $entity = data('entity', $eId);
    $args = $entity ? [$entity] : [];

    foreach ([$prefix . $eId . '_' . $action, $prefix . $action] as $callback) {
        if (is_callable($callback)) {
            allowed() ? $callback(...$args) : action_denied();
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
function & registry(string $id = null): ?array
{
    static $data = [];

    if ($id === null) {
        $old = $data;
        $data = [];

        return $old;
    }

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
        $data['data'] = __DIR__ . '/data';
        $data['i18n'] = __DIR__ . '/i18n';
        $data['lib'] = $public . '/lib';
        $data['log'] = $root . '/var/log';
        $data['sql'] = __DIR__ . '/sql';
        $data['template'] = __DIR__ . '/template';
        $data['theme'] = $public . '/theme';
        $data['tmp'] = $root . '/var/tmp';
        $data['xml'] = __DIR__ . '/xml';
    }

    if (empty($data[$dir])) {
        throw new InvalidArgumentException(_('Invalid path %s', $dir));
    }

    $id = trim($id, '/');

    return $data[$dir] . ($id ? '/' . $id : '');
}

/**
 * Dispatches one or multiple events
 *
 * Calls all listeners for given event until one listener returns true
 *
 * @param string|array $event
 * @param array $data
 *
 * @return void
 */
function event($event, array & $data): void
{
    foreach ((array) $event as $id) {
        foreach (listener($id) as $listener) {
            if (is_callable($listener) && $listener($data)) {
                break;
            }
        }
    }
}

/**
 * Retrieve listeners for specified event
 *
 * @param string $event
 *
 * @return array
 */
function listener(string $event): array
{
    $data = & registry('listener');

    if ($data === null) {
        $data = [];

        foreach (data_order(data('listener'), ['sort' => 'asc']) as $listener) {
            $data[$listener['event']][] = fqn('listener_' . $listener['id']);
        }
    }

    return $data[$event] ?? [];
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
    $data = & registry('i18n');

    if ($data === null) {
        $data = [];
        $data = data_load(path('i18n', data('i18n', 'lang') . '.php'));
    }

    if (!$key) {
        return '';
    }

    $key = $data[$key] ?? $key;

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
