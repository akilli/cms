<?php
declare(strict_types = 1);

namespace app;

use account;
use act;
use ent;
use http;
use session;
use DomainException;
use Throwable;

/**
 * Runs application
 */
function run(): void
{
    $act = http\req('act');
    $eId = http\req('ent');
    $args = ($ent = cfg('ent', $eId)) ? [$ent] : [];
    $func = 'act\\' . $eId . '_' . $act;
    $calls = $ent && array_key_exists($act, $ent['act']) ? [$func, 'act\\' . $act] : [$func];

    foreach ($calls as $call) {
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
 * @throws DomainException
 */
function path(string $dir, string $id = null): string
{
    if (empty(APP['path'][$dir])) {
        throw new DomainException(i18n('Invalid path %s', $dir));
    }

    return APP['path'][$dir] . ($id && ($id = trim($id, '/')) ? '/' . $id : '');
}

/**
 * Loads and returns configuration data
 *
 * @return mixed
 */
function cfg(string $id, string $key = null)
{
    if (($data = & data('cfg.' . $id)) === null) {
        $data = load($id);
        $data = event(['cfg.' . $id], $data);
    }

    if ($key === null) {
        return $data;
    }

    return $data[$key] ?? null;
}

/**
 * Loads configuration data with or without overwrites
 */
function load(string $id, bool $base = false): array
{
    $data = [];

    foreach ([path('cfg', $id . '.php'), path('ext', 'cfg/' . $id . '.php')] as $file) {
        if (is_readable($file) && ($data = array_replace_recursive($data, include $file)) && $base) {
            return $data;
        }
    }

    return $data;
}

/**
 * Dispatches multiple events with the the same event data
 */
function event(array $events, array $data): array
{
    foreach ($events as $event) {
        if (($cfg = cfg('listener', $event)) && asort($cfg, SORT_NUMERIC)) {
            foreach (array_keys($cfg) as $call) {
                $data = ('listener\\' . $call)($data);
            }
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
 * Converts special chars to HTML entities
 */
function enc(string $val): string
{
    return htmlspecialchars($val, ENT_QUOTES, ini_get('default_charset'), false);
}

/**
 * Converts a date, time or datetime from one to another format
 */
function datetime(string $val, string $in, string $out): string
{
    $val = $val ? date_create_from_format($in, $val) : date_create();

    return $val && ($val = date_format($val, $out)) ? $val : '';
}

/**
 * Returns layout section and optionally sets variables
 *
 * @throws DomainException
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
        throw new DomainException(i18n('Invalid section %s', $id));
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

    $§ = event(['section.' . $§['type'], 'layout.' . $id], $§);

    return ('section\\' . $§['type'])($§);
}

/**
 * Template path
 */
function tpl(string $id): string
{
    $ext = path('ext', 'tpl/' . $id);

    return is_file($ext) ? $ext : path('tpl', $id);
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
    if (!$path = trim($path, '/')) {
        return '/';
    }

    if ($path[0] === '#' || strpos($path, 'http') === 0) {
        return $path;
    }

    return '/' . resolve($path) . ($params ? '?' . http_build_query($params, '', '&amp;') : '');
}

/**
 * Asset URL
 *
 * @throws DomainException
 */
function asset(string $path): string
{
    $p = explode('/', trim($path, '/'));

    if (empty($p[0]) || empty($p[1])) {
        throw new DomainException(i18n('Invalid path %s', $path));
    }

    return '/' . $p[0] . '/asset/' . $p[1];
}

/**
 * Theme URL
 */
function theme(string $path): string
{
    return APP['url.theme'] . trim($path, '/');
}

/**
 * Rewrite URL
 */
function rewrite(string $path): string
{
    if (($cfg = cfg('url', $path)) && $cfg['redirect']) {
        http\redirect($cfg['target']);
    }

    if ($cfg) {
        return $cfg['target'];
    }

    $data = & data('url');

    if (empty($data[$path])) {
        $data[$path] = ($page = ent\one('page', [['url', $path]], ['select' => ['id']])) ? APP['url.page'] . $page['id'] : $path;
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
