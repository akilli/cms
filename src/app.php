<?php
declare(strict_types = 1);

namespace qnd;

use InvalidArgumentException;

/**
 * Constants
 */
const DATE_BACKEND = 'Y-m-d';
const DATE_FRONTEND = 'Y-m-d';
const DATETIME_BACKEND = 'Y-m-d H:i:s';
const DATETIME_FRONTEND = 'Y-m-d\TH:i';
const CSV = ['del' => ';', 'enc' => '"', 'esc' => '\\', 'single' => false, 'header' => false, 'keys' => []];
const IMPORT_HTML = '#<!-- IMPORT_START -->(.*)<!-- IMPORT_END -->#isU';
const IMPORT_TOC = 'toc.txt';
const PROJECT = 1;
const PRIVILEGE = '_all_';
const TIME_BACKEND = 'H:i:s';
const TIME_FRONTEND = 'H:i';
const URL = '.html';

/**
 * Runs application
 *
 * @return void
 */
function app(): void
{
    $prefix = fqn('action_');
    $action = request('action');
    $eId = request('entity');
    $entity = data('entity', $eId);
    $args = $entity ? [$entity] : [];

    foreach ([$prefix . $eId . '_' . $action, $prefix . $action] as $call) {
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

    $id = trim((string) $id, '/');

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

    $key = data('i18n', $key) ?? $key;

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
