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
    $data = & reg('app');
    $data['lang'] = locale_get_primary_language('');
    $parts = explode('/', trim(rewrite(http\req('url'), true), '/'));
    $data['ent'] = array_shift($parts);
    $data['act'] = array_shift($parts);
    $data['id'] = array_shift($parts);
    $data['path'] = $data['ent'] . '/' . $data['act'];
    $data['area'] = empty(cfg('priv', $data['path'])['active']) ? APP['area.public'] : APP['area.admin'];
    $allowed = allowed($data['path']);
    $ent = cfg('ent', $data['ent']);
    $real = is_callable('act\\' . $data['ent'] . '_' . $data['act']) ? 'act\\' . $data['ent'] . '_' . $data['act'] : null;

    if ($allowed && !$ent && $real) {
        $real();
    } elseif (!$allowed || !$ent || !isset($ent['act'][$data['act']])) {
        act\app_error();
    } elseif ($real) {
        $real($ent);
    } elseif ($ent['parent'] && is_callable('act\\' . $ent['parent'] . '_' . $data['act'])) {
        ('act\\' . $ent['parent'] . '_' . $data['act'])($ent);
    } elseif (is_callable('act\\' . $data['act'])) {
        ('act\\' . $data['act'])($ent);
    }
}

/**
 * Internal registry
 */
function & reg(string $id): ?array
{
    static $data = [];

    if (!array_key_exists($id, $data)) {
        $data[$id] = null;
    }

    return $data[$id];
}

/**
 * Returns app data
 *
 * @return mixed
 */
function data(string $id)
{
    return reg('app')[$id] ?? null;
}

/**
 * Loads and returns configuration data
 *
 * @return mixed
 */
function cfg(string $id, string $key = null)
{
    if (($data = & reg('cfg.' . $id)) === null) {
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

    foreach ([path('cfg', $id . '.php'), path('ext.cfg', $id . '.php')] as $file) {
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
 * Check access to given URL considering rewrites
 */
function allowed_url(string $path): bool
{
    if (preg_match('#^https?://#', $path)) {
        return true;
    }

    $parts = explode('/', ltrim(rewrite($path), '/'));

    return cfg('ent', $parts[0]) && !empty($parts[1]) && allowed($parts[0] . '/' . $parts[1]);
}

/**
 * Returns layout section and optionally sets variables
 *
 * @throws DomainException
 */
function layout(string $id = null, array $vars = null): array
{
    if (($data = & reg('layout')) === null) {
        $cfg = cfg('layout');
        $data = [];
        $ent = cfg('ent', data('ent'));
        $act = data('act');
        $path = data('path');
        $area = data('area');

        if (http_response_code() === 404) {
            $keys = [APP['all'], $area, 'app/error'];
        } elseif (!empty($ent['parent'])) {
            $keys = [APP['all'], $area, $act, $ent['parent'] . '/' . $act, $path];
        } else {
            $keys = [APP['all'], $area, $act, $path];
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
 * Template path
 */
function tpl(string $id): string
{
    $ext = path('ext.tpl', $id);

    return is_file($ext) ? $ext : path('tpl', $id);
}

/**
 * Generate URL by given path and params
 */
function url(string $path = '', array $params = []): string
{
    $p = $params ? '?' . http_build_query($params, '', '&amp;') : '';

    return '/' . trim($path, '/') . $p;
}

/**
 * GUI URL
 */
function gui(string $path): string
{
    $data = & reg('gui');
    $data['app'] = $data['app'] ?? filemtime(path('gui'));

    return APP['url.gui'] . $data['app'] . '/' . trim($path, '/');
}

/**
 * Extension GUI URL
 */
function ext(string $path): string
{
    $data = & reg('gui');
    $data['ext'] = $data['ext'] ?? filemtime(path('ext.gui'));

    return APP['url.ext'] . $data['ext'] . '/' . trim($path, '/');
}

/**
 * Rewrite
 */
function rewrite(string $path, bool $redirect = false): string
{
    $url = ent\one('url', [['name', $path]]);

    if ($redirect && !empty($url['redirect'])) {
        redirect($url['target'], $url['redirect']);
    }

    if ($url) {
        return $url['target'];
    }

    if ($page = ent\one('page', [['url', $path]])) {
        return '/' . $page['ent'] . '/view/' . $page['id'];
    }

    return $path;
}

/**
 * Redirect
 */
function redirect(string $url = '/', int $code = 302): void
{
    header('Location: ' . $url, true, in_array($code, APP['code']) ? $code : 302);
    exit;
}

/**
 * Converts special chars to HTML entities
 */
function enc(string $val): string
{
    return htmlspecialchars($val, ENT_QUOTES, ini_get('default_charset'), false);
}

/**
 * Logger
 */
function log(Throwable $e): void
{
    file_put_contents(APP['log'], '[' . date('r') . '] ' . $e . "\n\n", FILE_APPEND);
}
