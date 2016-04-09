<?php
namespace akilli;

use InvalidArgumentException;

/**
 * Runs application
 *
 * @param array $data
 *
 * @return void
 */
function app(array $data = [])
{
    // Dispatch action event
    $event = 'action.' . request('id');

    if (!$listeners = listener($event)) {
        $event = 'action.' . request('action');
        $listeners = listener($event);
    }

    if (!$listeners) {
        $event = 'action.error';
    } elseif (!allowed()) {
        $event = 'action.denied';
    }

    event($event, $data);

    // Send response
    echo render('root');
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
        // app
        $data['app'] = $data['root'] . '/app';
        $data['db'] = $data['app'] . '/db';
        $data['log'] = $data['app'] . '/log';
        // src
        $data['src'] = __DIR__;
        $data['data'] = $data['src'] . '/data';
        $data['template'] = $data['src'] . '/template';
        // public
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
 * @param string|array $event
 * @param array $data
 *
 * @return void
 */
function event($event, array & $data)
{
    foreach ((array) $event as $id) {
        // Calls all listeners for given event until one listener returns true
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

        foreach (data_order(data('listener'), 'sort_order') as $listener) {
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
    static $data;

    if ($data === null) {
        $data = array_replace(data('i18n.' . config('i18n.language')), data('i18n.' . config('i18n.locale')));
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
