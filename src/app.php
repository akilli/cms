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
    $prefix = fqn('action_');

    foreach ([$prefix . request('entity') . '_' . request('action'), $prefix . request('action')] as $action) {
        if (is_callable($action)) {
            allowed() ? $action() : action_denied();
            goto response;
        }
    }

    action_error();
    response: echo §('root');
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
        $data['src'] = __DIR__;
        $data['data'] = $data['src'] . '/data';
        $data['template'] = $data['src'] . '/template';
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
            $data[$listener['event']][] = $listener['id'];
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
    return __NAMESPACE__ . '\\' . $name;
}
