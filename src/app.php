<?php
declare(strict_types = 1);

namespace qnd;

use InvalidArgumentException;

/**
 * Constants
 */
const ADMIN = ['proj' => 1, 'priv' => '_all_'];
const CRIT = [
    '=' => '=',
    '!=' => '!=',
    '>' => '>',
    '>=' => '>=',
    '<' => '>',
    '<=' => '<=',
    '~' => '~',
    '!~' => '!~',
    '~^' => '~^',
    '!~^' => '!~^',
    '~$' => '~$',
    '!~$' => '!~$',
];
const DATE = ['b' => 'Y-m-d', 'f' => 'Y-m-d'];
const DATETIME = ['b' => 'Y-m-d H:i:s', 'f' => 'Y-m-d\TH:i'];
const IMPORT = ['toc' => 'toc.txt', 'del' => ';', 'start' => '<!-- IMPORT_START -->', 'end' => '<!-- IMPORT_END -->'];
const LOG = 'qnd.log';
const OPTS = ['mode' => 'all', 'index' => 'id', 'select' => [], 'order' => [], 'limit' => 0, 'offset' => 0];
const TIME = ['b' => 'H:i:s', 'f' => 'H:i'];
const URL = '.html';

/**
 * Runs application
 *
 * @return void
 */
function app(): void
{
    $prefix = fqn('action_');
    $act = request('action');
    $eId = request('entity');
    $entity = data('entity', $eId);
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
        $data['asset'] = '/data';
        $data['data'] = $root . '/data';
        $data['log'] = '/var/log/app';
        $data['template'] = $root . '/template';
        $data['theme'] = $root . '/public/theme';
        $data['tmp'] = '/tmp';
        $data['media'] = $data['asset'] . '/' . project('id');
    }

    if (empty($data[$dir])) {
        throw new InvalidArgumentException(_('Invalid path %s', $dir));
    }

    return $data[$dir] . ($id && ($id = trim($id, '/')) ? '/' . $id : '');
}

/**
 * Project
 *
 * @param string $key
 *
 * @return mixed
 */
function project(string $key)
{
    $data = & registry('project');

    if ($data === null) {
        $data = [];
        $id = (int) session('project');
        $crit = $id ? [['id', $id]] : [['uid', strstr(request('host'), '.', true)]];
        $crit[] = ['active', true];
        $data = one('project', $crit) ?: one('project', [['id', ADMIN['proj']]]);
        session('project', $data['id']);
    }

    return $data[$key] ?? null;
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
        $data = arr_load(path('data', $section . '.php'));
        $data = event('data.load.' . $section, $data);
    }

    if ($id === null) {
        return $data;
    }

    return $data[$id] ?? null;
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
 * Logger
 *
 * @param string $message
 *
 * @return void
 */
function logger(string $message): void
{
    file_put_contents(path('log', LOG), '[' . date('r') . '] ' . $message . "\n\n", FILE_APPEND);
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
