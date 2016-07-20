<?php
namespace qnd;

use InvalidArgumentException;

/**
 * Constants
 */
const PROJECT = 'base';
const THEME = 'base';
const USER = 1;

/**
 * Runs application
 *
 * @return void
 */
function app()
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
function & registry(string $id = null)
{
    static $data = [];

    if ($id === null) {
        $data = [];

        return $data;
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
 * @param string $subpath
 *
 * @return string
 *
 * @throws InvalidArgumentException
 */
function path(string $dir, string $subpath = ''): string
{
    $data = & registry('path');

    if ($data === null) {
        $data = [];
        $root = filter_path(realpath(__DIR__ . '/..'));
        $public = filter_path(realpath(dirname($_SERVER['SCRIPT_FILENAME'])));
        $data['asset'] = $public . '/asset';
        $data['data'] = __DIR__ . '/data';
        $data['lib'] = $public . '/lib';
        $data['log'] = $root . '/var/log';
        $data['template'] = __DIR__ . '/template';
        $data['theme'] = $public . '/theme';
        $data['tmp'] = $root . '/var/tmp';
        $data['xml'] = __DIR__ . '/xml';
    }

    if (empty($data[$dir])) {
        throw new InvalidArgumentException(_('Invalid path %s', $dir));
    }

    return rtrim($data[$dir] . '/' . $subpath, '/');
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
function event($event, array & $data)
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
        $data = data('i18n.' . data('i18n', 'locale')) ?: data('i18n.' . data('i18n', 'lang'));
    }

    if (!$key) {
        return '';
    }

    if (isset($data[$key])) {
        $key = $data[$key];
    }

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
