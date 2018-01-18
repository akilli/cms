<?php
declare(strict_types = 1);

namespace app;

use account;
use act;
use arr;
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
    $allowed = allowed($eId . '/' . $act);
    $ent = cfg('ent', $eId);
    $full = is_callable('act\\' . $eId . '_' . $act) ? 'act\\' . $eId . '_' . $act : null;
    $fallback = is_callable('act\\' . $act) ? 'act\\' . $act : null;

    if ($allowed && !$ent && $full) {
        $full();
    } elseif (!$allowed || !isset($ent['act'][$act])) {
        act\app_error();
    } elseif ($full) {
        $full($ent);
    } elseif ($fallback) {
        $fallback($ent);
    }
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
function load(string $id): array
{
    $data = [];

    foreach ([path('cfg', $id . '.php'), path('ext', 'cfg/' . $id . '.php')] as $file) {
        if (is_readable($file)) {
            $data = array_replace_recursive($data, include $file);
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
 * Returns layout section and optionally sets variables
 *
 * @throws DomainException
 */
function layout(string $id = null, array $vars = null): array
{
    if (($data = & data('layout')) === null) {
        $cfg = cfg('layout');
        $data = [];
        $path = http\req('path');
        $area = empty(cfg('priv', $path)['active']) ? APP['layout.public'] : APP['layout.admin'];
        $keys = [APP['all'], $area];

        if (http_response_code() === 404) {
            $keys[] = 'app/error';
        } else {
            $keys[] = http\req('act');
            $keys[] = $path;
        }

        foreach ($keys as $key) {
            if (!empty($cfg[$key])) {
                $data = array_replace_recursive($data, $cfg[$key]);
            }
        }

        foreach ($data as $key => $val) {
            $data[$key] = arr\replace(APP['section'], $val, ['id' => $key]);
        }
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
    if (!$cfg = cfg('priv', $key)) {
        return false;
    }

    return !$cfg['active'] || $cfg['priv'] && allowed($cfg['priv']) || account\data('admin') || in_array($key, account\data('priv'));
}

/**
 * Generate URL by given path and params
 */
function url(?string $path = '', array $params = []): string
{
    $p = $params ? '?' . http_build_query($params, '', '&amp;') : '';

    return ($path === null ? '' : '/' . trim($path, '/')) . $p;
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
 * GUI URL
 */
function gui(string $path): string
{
    return APP['url.gui'] . trim($path, '/');
}

/**
 * Extension GUI URL
 */
function ext(string $path): string
{
    return APP['url.ext'] . trim($path, '/');
}

/**
 * Rewrite URL
 */
function rewrite(string $path): string
{
    if ($url = ent\one('url', [['name', $path]])) {
        return $url['target'];
    }

    if ($page = ent\one('page', [['url', $path]])) {
        return APP['url.page'] . $page['id'];
    }

    return $path;
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
 * Logger
 */
function log(Throwable $e): void
{
    file_put_contents(APP['log'], '[' . date('r') . '] ' . $e . "\n\n", FILE_APPEND);
}
