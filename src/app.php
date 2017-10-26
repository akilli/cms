<?php
declare(strict_types = 1);

namespace app;

use account;
use act;
use ent;
use file;
use http;
use session;
use ErrorException;
use RuntimeException;
use Throwable;

/**
 * Runs application
 */
function run(): void
{
    $ns = 'act\\';
    $act = http\req('act');
    $eId = http\req('ent');
    $args = ($ent = cfg('ent', $eId)) ? [$ent] : [];

    foreach ([$ns . $eId . '_' . $act, $ns . $act] as $call) {
        if (is_callable($call)) {
            allowed('*/*') ? $call(...$args) : act\app_denied();
            return;
        }
    }

    act\app_error();
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
 * @throws RuntimeException
 */
function path(string $dir, string $id = null): string
{
    if (($data = & data('path')) === null) {
        $root = dirname(__DIR__);
        $data = ['cfg' => $root .'/cfg', 'data' => '/data', 'theme' => $root .'/www/theme', 'tpl' => $root .'/tpl'];
    }

    if (empty($data[$dir])) {
        throw new RuntimeException(i18n('Invalid path %s', $dir));
    }

    return $data[$dir] . ($id && ($id = trim($id, '/')) ? '/' . $id : '');
}

/**
 * Loads and returns configuration data
 *
 * @return mixed
 */
function cfg(string $id, string $key = null)
{
    if (($data = & data('cfg.' . $id)) === null) {
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
            $data = ('listener\\' . $call)($data);
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
 * Returns layout section and optionally sets variables
 *
 * @throws RuntimeException
 */
function layout(string $id = null, array $vars = null): array
{
    if (($data = & data('layout')) === null) {
        $data = cfg('layout');
    }

    // Get whole layout
    if ($id === null) {
        return $data;
    }

    // Invalid section
    if (empty($data[$id])) {
        throw new RuntimeException(i18n('Invalid section %s', $id));
    }

    // Add variables to section
    if ($vars) {
        $data[$id]['vars'] = array_replace($data[$id]['vars'], $vars);
    }

    return $data[$id];
}

/**
 * Render section
 */
function §(string $id): string
{
    $§ = layout($id);

    if (!$§['active'] || $§['priv'] && !allowed($§['priv'])) {
        return '';
    }

    $§ = event('section.' . $§['section'], $§);
    $§ = event('layout.section.' . $id, $§);

    return ('section\\' . $§['section'])($§);
}

/**
 * Check access
 */
function allowed(string $key): bool
{
    $key = resolve($key);

    if (!$cfg = cfg('priv', $key)) {
        return false;
    }

    return !$cfg['active'] || $cfg['priv'] && allowed($cfg['priv']) || account\data('admin') || in_array($key, account\data('priv'));
}

/**
 * Check access to given URL considering rewrites
 */
function allowed_url(string $path): bool
{
    if (strpos($path, 'http') === 0) {
        return true;
    }

    $parts = explode('/', ltrim(rewrite($path), '/'));

    return cfg('ent', $parts[0]) && !empty($parts[1]) && allowed($parts[0] . '/' . $parts[1]);
}

/**
 * Resolves wildcards, i.e. asterisks, for entity and action part with appropriate values from current request
 */
function resolve(string $key): string
{
    return preg_replace(['#^\*/#', '#^([^/]+)/\*($|/)#'], [http\req('ent') . '/', '$1/' . http\req('act') . '$2'], $key);
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
    return APP['url.asset'] . $path;
}

/**
 * Media URL
 */
function media(string $path): string
{
    return APP['url.media'] . $path;
}

/**
 * Theme URL
 */
function theme(string $path): string
{
    return APP['url.theme'] . $path;
}

/**
 * Rewrite URL
 */
function rewrite(string $path): string
{
    if ($url = cfg('url', $path)) {
        return $url;
    }

    if (!preg_match('#' . APP['url.ext'] . '$#', $path)) {
        return $path;
    }

    $data = & data('url');

    if (empty($data[$path])) {
        $data[$path] = ($page = ent\one('page', [['url', $path]])) ? APP['url.page'] . $page['id'] : $path;
    }

    return $data[$path];
}

/**
 * Logger
 */
function log(Throwable $e): void
{
    file_put_contents(APP['log'], '[' . date('r') . '] ' . $e . "\n\n", FILE_APPEND);
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
