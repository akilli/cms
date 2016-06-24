<?php
namespace qnd;

use InvalidArgumentException;

/**
 * Runs application
 *
 * @return void
 */
function app()
{
    if ($entity = data('entity', request('entity'))) {
        $prefix = fqn('action_');
        $action = request('action');

        foreach ([$prefix . $entity['id'] . '_' . $action, $prefix . $action] as $callback) {
            if (is_callable($callback)) {
                allowed() ? $callback($entity) : action_denied();
                return;
            }
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
function & registry(string $id)
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
 * @param string $subpath
 *
 * @return string
 *
 * @throws InvalidArgumentException
 */
function path(string $dir, string $subpath = null): string
{
    $data = & registry('path');

    if ($data === null) {
        $data = [];
        $data['root'] = filter_path(realpath(__DIR__ . '/..'));
        $data['app'] = $data['root'] . '/app';
        $data['db'] = $data['app'] . '/db';
        $data['log'] = $data['app'] . '/log';
        $data['tmp'] = $data['app'] . '/tmp';
        $data['src'] = __DIR__;
        $data['data'] = $data['src'] . '/data';
        $data['template'] = $data['src'] . '/template';
        $data['xml'] = $data['src'] . '/xml';
        $data['public'] = filter_path(realpath(dirname($_SERVER['SCRIPT_FILENAME'])));
        $data['asset'] = $data['public'] . '/asset';
        $data['cache'] = $data['asset'] . '/cache';
        $data['css'] = $data['asset'] . '/css';
        $data['js'] = $data['asset'] . '/js';
        $data['media'] = $data['asset'] . '/media';
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
 * Config
 *
 * @param string $key
 *
 * @return mixed
 */
function config(string $key)
{
    return data('config')[$key] ?? null;
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
        $data = array_replace(data('i18n.' . config('i18n.lang')), data('i18n.' . config('i18n.locale')));
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
