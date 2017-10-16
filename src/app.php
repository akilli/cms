<?php
declare(strict_types = 1);

namespace app;

use function http\req;
use function layout\§;
use account;
use act;
use ent;
use file;
use session;
use ErrorException;
use InvalidArgumentException;
use Throwable;

const ALL = '_all_';
const LOG = 'php://stdout';
const URL = ['asset' => '/asset/', 'media' => '/media/view/', 'page' => '.html', 'theme' => '/theme/'];

/**
 * Runs application
 */
function run(): void
{
    $prefix = 'act\\';
    $act = req('act');
    $eId = req('ent');
    $ent = cfg('ent', $eId);
    $args = $ent ? [$ent] : [];

    foreach ([$prefix . $eId . '_' . $act, $prefix . $act] as $call) {
        if (is_callable($call)) {
            account\allowed('*/*') ? $call(...$args) : act\app_denied();
            return;
        }
    }

    act\app_error();
    echo §('root');
}

/**
 * Internal registry
 */
function & data(string $id): ?array
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
 * @throws InvalidArgumentException
 */
function path(string $dir, string $id = null): string
{
    $data = & data('path');

    if ($data === null) {
        $root = dirname(__DIR__);
        $data['cfg'] = $root .'/cfg';
        $data['data'] = '/data';
        $data['theme'] = $root .'/www/theme';
        $data['tpl'] = $root .'/tpl';
    }

    if (empty($data[$dir])) {
        throw new InvalidArgumentException(i18n('Invalid path %s', $dir));
    }

    return $data[$dir] . ($id && ($id = trim($id, '/')) ? '/' . $id : '');
}

/**
 * Loads and returns configuration data
 */
function cfg(string $id, string $key = null)
{
    $data = & data('cfg.' . $id);

    if ($data === null) {
        $data = file\load(path('cfg', $id . '.php'));
        $data = event('cfg.' . $id, $data);
    }

    if ($key === null) {
        return $data;
    }

    return $data[$key] ?? null;
}

/**
 * Dispatches an event
 */
function event(string $event, array $data): array
{
    if (($listeners = cfg('listener', $event)) && asort($listeners, SORT_NUMERIC)) {
        foreach (array_keys($listeners) as $call) {
            $data = $call($data);
        }
    }

    return $data;
}

/**
 * Add message
 */
function msg(string $msg): void
{
    $data = session\get('msg') ?? [];

    if ($msg && !in_array($msg, $data)) {
        $data[] = $msg;
        session\set('msg', $data);
    }
}

/**
 * Translate
 */
function i18n(string $key, string ...$args): string
{
    $key = cfg('i18n', $key) ?? $key;

    return $args ? vsprintf($key, $args) : $key;
}

/**
 * Resolves wildcards, i.e. asterisks, for entity and action part with appropriate values from current request
 */
function resolve(string $path): string
{
    return preg_replace(['#^\*/#', '#^([^/]+)/\*($|/)#'], [req('ent') . '/', '$1/' . req('act') . '$2'], $path);
}

/**
 * Generate URL by given path and params
 */
function url(string $path = '', array $params = []): string
{
    if ($path && ($path[0] === '#' || strpos($path, 'http') === 0)) {
        return $path;
    }

    if (!$path = trim($path, '/')) {
        return '/';
    }

    return '/' . resolve($path) . ($params ? '?' . http_build_query($params, '', '&amp;') : '');
}

/**
 * Asset URL
 */
function asset(string $path): string
{
    return URL['asset'] . $path;
}

/**
 * Media URL
 */
function media(string $path): string
{
    return URL['media'] . $path;
}

/**
 * Theme URL
 */
function theme(string $path): string
{
    return URL['theme'] . $path;
}

/**
 * Rewrite URL
 */
function rewrite(string $path): string
{
    if ($path === '/') {
        return (string) cfg('app', 'home');
    }

    if (!preg_match('#' . URL['page'] . '$#', $path)) {
        return $path;
    }

    $data = & data('url');

    if (empty($data[$path])) {
        $data[$path] = ($page = ent\one('page', [['url', $path]])) ? '/page/view/' . $page['id'] : $path;
    }

    return $data[$path];
}

/**
 * Logger
 */
function log(Throwable $e): void
{
    file_put_contents(LOG, '[' . date('r') . '] ' . $e . "\n\n", FILE_APPEND);
}

/**
 * Error Handler
 */
function error(int $severity, string $msg, string $file, int $line): void
{
    log(new ErrorException($msg, 0, $severity, $file, $line));
}

/**
 * Exception Handler
 */
function exception(Throwable $e): void
{
    log($e);
}
