<?php
declare(strict_types = 1);

namespace cms;

use InvalidArgumentException;

/**
 * Constants
 */
const ALL = ['layout' => '_all_', 'privilege' => '_all_', 'project' => 1];
const ATTR = [
    'id' => null,
    'name' => null,
    'col' => null,
    'auto' => false,
    'sort' => 0,
    'type' => null,
    'backend' => null,
    'frontend' => null,
    'db_type' => null,
    'pdo' => null,
    'nullable' => false,
    'required' => false,
    'uniq' => false,
    'multiple' => false,
    'searchable' => false,
    'opt' => [],
    'actions' => [],
    'val' => null,
    'min' => 0,
    'max' => 0,
    'minlength' => 0,
    'maxlength' => 0,
    'entity' => null,
    'html' => [],
    'validator' => null,
    'saver' => null,
    'loader' => null,
    'editor' => null,
    'viewer' => null,
];
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
const LOG = 'cms.log';
const OPTS = ['mode' => 'all', 'index' => 'id', 'select' => [], 'order' => [], 'limit' => 0, 'offset' => 0];
const SECTION = [
    'id' => null,
    'section' => null,
    'template' => null,
    'vars' => [],
    'active' => true,
    'privilege' => null,
    'parent' => 'root',
    'sort' => 0,
    'children' => [],
];
const TIME = ['b' => 'H:i:s', 'f' => 'H:i'];
const URL = ['asset' => '/asset/', 'media' => '/media/view/', 'page' => '.html', 'theme' => '/theme/'];

/**
 * Runs application
 *
 * @return void
 */
function app(): void
{
    $prefix = __NAMESPACE__ . '\\' . 'action_';
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
        $data['asset'] = '/data';
        $data['data'] = '/app/data';
        $data['log'] = '/var/log/app';
        $data['template'] = '/app/template';
        $data['theme'] = '/app/public/theme';
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
function project(string $key = null)
{
    $data = & registry('project');

    if ($data === null) {
        $data = [];
        $crit = [['active', true]];
        $crit[] = ($id = session_get('project')) ? ['id', $id] : ['uid', strstr(request('host'), '.', true)];
        $data = one('project', $crit) ?: one('project', [['id', ALL['project']]]);
        $data['global'] = $data['id'] === ALL['project'];
        $data['image'] = url_theme('logo.jpg');
        session_set('project', $data['id']);
    }

    if ($key === null) {
        return $data;
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
            $data = $call($data);
        }
    }

    return $data;
}

/**
 * Translate
 *
 * @param string $key
 * @param string[] ...$args
 *
 * @return string
 */
function _(string $key, string ...$args): string
{
    if (!$key) {
        return '';
    }

    $key = data('i18n', $key) ?? $key;

    if (!$args) {
        return $key;
    }

    return vsprintf($key, $args) ?: $key;
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
