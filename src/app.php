<?php
namespace app;

use data;
use file;
use filter;
use http;
use i18n;
use role;
use view;
use InvalidArgumentException;

/**
 * Runs application
 *
 * @param array $data
 *
 * @return void
 */
function run(array $data = [])
{
    // Dispatch action event
    $event = 'action.' . http\request('id');

    if (!$listeners = listener($event)) {
        $event = 'action.' . http\request('action');
        $listeners = listener($event);
    }

    if (!$listeners) {
        $event = 'action.error';
    } elseif (!role\allowed()) {
        $event = 'action.denied';
    }

    event($event, $data);

    // Send response
    echo view\render('root');
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
        $data['root'] = filter\path(realpath(__DIR__ . '/..'));
        // Local
        $data['app'] = $data['root'] . '/app';
        $data['db'] = $data['app'] . '/db';
        $data['log'] = $data['app'] . '/log';
        // Source
        $data['src'] = __DIR__;
        $data['data'] = $data['src'] . '/data';
        $data['template'] = $data['src'] . '/template';
        // Public
        $data['public'] = filter\path(realpath(dirname($_SERVER['SCRIPT_FILENAME'])));
        $data['asset'] = $data['public'] . '/asset';
        $data['cache'] = $data['asset'] . '/cache';
        $data['css'] = $data['asset'] . '/css';
        $data['js'] = $data['asset'] . '/js';
        $data['media'] = $data['asset'] . '/media';
    }

    if (empty($data[$dir])) {
        throw new InvalidArgumentException(i18n\translate('Invalid path %s', $dir));
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

        foreach (data\order(data('listener'), 'sort_order') as $listener) {
            $data[$listener['event']][] = $listener['id'];
        }
    }

    return $data[$event] ?? [];
}

/**
 * Template registry
 *
 * @param string $id
 *
 * @return string|null
 */
function template(string $id)
{
    $file = path('template', $id);

    return is_readable($file) ? $file : null;
}

/**
 * Data
 *
 * @param string $section
 * @param string $id
 *
 * @return mixed
 */
function data(string $section, string $id = null)
{
    $data = & registry('data.' . $section);

    if ($data === null) {
        $data = [];

        // Load data from file
        $data = data\load(path('data', $section . '.php'));

        // Dispatch load event
        if ($section !== 'listener') {
            event('data.load.' . $section, $data);
        }
    }

    if ($id === null) {
        return $data;
    }

    return $data[$id] ?? null;
}
