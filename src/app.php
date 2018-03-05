<?php
declare(strict_types = 1);

namespace app;

use account;
use act;
use arr;
use ent;
use req;
use session;
use DomainException;
use ErrorException;
use Throwable;

/**
 * Runs application
 */
function run(): void
{
    // Register functions
    set_error_handler('app\error');
    set_exception_handler('app\exception');
    register_shutdown_function('app\shutdown');

    $data = & reg('app');
    $data['lang'] = locale_get_primary_language('');
    $url = req\data('url');
    $rew = ent\one('url', [['name', $url]]);

    // Redirect
    if (!empty($rew['redirect'])) {
        redirect($rew['target'], $rew['redirect']);
        return;
    }

    // Rewrite
    if ($rew) {
        $url = $rew['target'];
    } elseif ($page = ent\one('page', [['url', $url]])) {
        $url = '/' . $page['ent'] . '/view/' . $page['id'];
    }

    // Gather request-data
    $parts = explode('/', trim($url, '/'));
    $data['ent'] = ($eId = array_shift($parts)) ? cfg('ent', $eId) : null;
    $data['act'] = array_shift($parts);
    $data['id'] = array_shift($parts);
    $data['path'] = $eId . '/' . $data['act'];
    $data['area'] = empty(cfg('priv', $data['path'])['active']) ? APP['area.public'] : APP['area.admin'];
    $data['parent'] = $data['ent']['parent'] ?? null;
    $allowed = allowed($data['path']);
    $real = is_callable('act\\' . $eId . '_' . $data['act']) ? 'act\\' . $eId . '_' . $data['act'] : null;

    // Dispatch request
    if ($allowed && !$data['ent'] && $real) {
        $real();
    } elseif (!$allowed || !$data['ent'] || !in_array($data['act'], $data['ent']['act'])) {
        act\app_error();
    } elseif ($real) {
        $real($data['ent']);
    } elseif ($data['ent']['parent'] && is_callable('act\\' . $data['ent']['parent'] . '_' . $data['act'])) {
        ('act\\' . $data['ent']['parent'] . '_' . $data['act'])($data['ent']);
    } elseif (is_callable('act\\' . $data['act'])) {
        ('act\\' . $data['act'])($data['ent']);
    }
}

/**
 * Shutdown
 */
function shutdown() {
    if ($data = reg('msg')) {
        session\set('msg', $data);
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
        if (($cfg = cfg('event', $event)) && asort($cfg, SORT_NUMERIC)) {
            foreach (array_keys($cfg) as $call) {
                $data = $call($data);
            }
        }
    }

    return $data;
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
 * Translate
 */
function i18n(string $key, string ...$args): string
{
    $key = cfg('i18n', $key) ?? $key;

    return $args ? vsprintf($key, $args) : $key;
}

/**
 * Message
 */
function msg(string $msg = null, string ...$args): array
{
    if (($data = & reg('msg')) === null) {
        $data = session\get('msg') ?: [];
        session\set('msg', null);
    }

    if ($msg === null) {
        $old = $data;
        $data = [];

        return $old;
    }

    if ($msg && ($msg = i18n($msg, ...$args)) && !in_array($msg, $data)) {
        $data[] = $msg;
    }

    return $data;
}

/**
 * Logger
 */
function log(Throwable $e): void
{
    file_put_contents(APP['log'], '[' . date('r') . '] ' . $e . "\n\n", FILE_APPEND);
}

/**
 * Error handler
 */
function error(int $severity, string $msg, string $file, int $line): void
{
    log(new ErrorException($msg, 0, $severity, $file, $line));
}

/**
 * Exception handler
 */
function exception(Throwable $e): void
{
    log($e);
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
 * Returns layout block and optionally sets variables
 *
 * @throws DomainException
 */
function layout(string $id = null, array $vars = null): array
{
    if (($data = & reg('layout')) === null) {
        $cfg = cfg('layout');
        $data = [];
        $parent = data('parent');
        $act = data('act');
        $path = data('path');
        $area = data('area');

        if (http_response_code() === 404) {
            $keys = [APP['all'], $area, 'app/error'];
        } elseif ($parent) {
            $keys = [APP['all'], $area, $act, $parent . '/' . $act, $path];
        } else {
            $keys = [APP['all'], $area, $act, $path];
        }

        foreach ($keys as $key) {
            if (!empty($cfg[$key])) {
                $data = array_replace_recursive($data, $cfg[$key]);
            }
        }

        foreach ($data as $key => $val) {
            $data[$key] = arr\replace(APP['block'], $val, ['id' => $key]);
        }
    }

    if ($id === null) {
        return $data;
    }

    if (empty($data[$id])) {
        throw new DomainException(i18n('Invalid block %s', $id));
    }

    if ($vars) {
        $data[$id]['vars'] = array_replace($data[$id]['vars'], $vars);
    }

    return $data[$id];
}

/**
 * Render block
 */
function §(string $id): string
{
    $§ = layout($id);

    if (!$§['active'] || $§['priv'] && !allowed($§['priv'])) {
        return '';
    }

    $§ = event(['block.' . $§['type'], 'layout.' . $id], $§);

    return $§['type']($§);
}

/**
 * Converts special chars to HTML entities
 */
function enc(string $val): string
{
    return htmlspecialchars($val, ENT_QUOTES, ini_get('default_charset'), false);
}

/**
 * Generates URL by given path and params, optionally preserves existing params
 */
function url(string $path = '', array $get = [], bool $preserve = false): string
{
    if ($preserve) {
        $get = array_replace(req\data('get'), $get);
    }

    return '/' . trim($path, '/') . ($get ? '?' . http_build_query($get, '', '&amp;') : '');
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
 * Redirect
 */
function redirect(string $url = '/', int $code = null): void
{
    if ($code && !empty(cfg('opt', 'redirect')[$code])) {
        header('Location: ' . $url, true, $code);
    } else {
        header('Location: ' . $url);
    }

    exit;
}
