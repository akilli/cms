<?php
declare(strict_types = 1);

namespace app;

use account;
use arr;
use entity;
use request;
use session;
use DomainException;
use Throwable;

/**
 * Runs application
 */
function run(): void
{
    // Register functions
    set_error_handler('handler\error');
    set_exception_handler('handler\exception');
    register_shutdown_function('handler\shutdown');

    $app = & registry('app');
    $app['lang'] = locale_get_primary_language('');
    $app['gui'] = max(filemtime(path('gui')), file_exists(path('ext.gui')) ? filemtime(path('ext.gui')) : 0);
    $app['error'] = false;
    $app['layout'] = null;
    $url = request\get('url');

    // Page
    if ($app['page'] = entity\one('page', [['url', $url]])) {
        $url = '/' . $app['page']['entity'] . '/view/' . $app['page']['id'];
        $app['layout'] = $app['page']['layout'];
    }

    // Gather request-data
    $parts = explode('/', trim($url, '/'));
    $app['entity'] = array_shift($parts);
    $entity = cfg('entity', $app['entity']);
    $app['action'] = array_shift($parts);
    $app['id'] = array_shift($parts);
    $app['area'] = empty(cfg('priv', $app['entity'] . '/' . $app['action'])['active']) ? '_public_' : '_admin_';
    $app['parent'] = $entity['parent'] ?? null;
    $host = preg_replace('#^www\.#', '', request\get('host'));
    $blacklist = $app['area'] === '_admin_' && in_array($host, cfg('app', 'admin.blacklist'));
    $allowed = !$blacklist && allowed($app['entity'] . '/' . $app['action']);
    $ns = 'action\\';
    $real = is_callable($ns . $app['entity'] . '_' . $app['action']) ? $ns . $app['entity'] . '_' . $app['action'] : null;

    // Dispatch request
    if ($allowed && !$entity && $real) {
        $real();
    } elseif (!$allowed || !$entity || !in_array($app['action'], $entity['action']) || $app['area'] === '_public_' && (!$app['page'] || $app['page']['disabled'])) {
        error();
    } elseif ($real) {
        $real($entity);
    } elseif ($app['parent'] && is_callable($ns . $app['parent'] . '_' . $app['action'])) {
        ($ns . $app['parent'] . '_' . $app['action'])($entity);
    } elseif (is_callable($ns . $app['action'])) {
        ($ns . $app['action'])($entity);
    }
}

/**
 * Handles invalid reuqests
 */
function error(): void
{
    http_response_code(404);
    $app = & registry('app');
    $app['error'] = true;
    $layout = & registry('layout');
    $layout = null;
}

/**
 * Internal registry
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
 * Returns app data
 *
 * @return mixed
 */
function get(string $id)
{
    return registry('app')[$id] ?? null;
}

/**
 * Loads and returns configuration data
 *
 * @note Config data must be cacheable, you must not do any dynamic/request-dependant stuff here
 *
 * @return mixed
 */
function cfg(string $id, string $key = null)
{
    if (($data = & registry('cfg.' . $id)) === null) {
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
    $file = path('cfg', $id . '.php');
    $data = is_readable($file) ? include $file : [];
    $extFile = path('ext.cfg', $id . '.php');

    if (!is_readable($extFile) || !($ext = include $extFile)) {
        return $data;
    }

    if (in_array($id, ['attr', 'entity'])) {
        return $data + $ext;
    }

    if ($id === 'layout') {
        return load_layout($data, $ext);
    }

    return array_replace_recursive($data, $ext);
}

/**
 * Load layout configuration
 */
function load_layout(array $data, array $ext = []): array
{
    foreach ($ext as $key => $cfg) {
        foreach ($cfg as $id => $§) {
            $data[$key][$id] = empty($data[$key][$id]) ? $§ : load_block($data[$key][$id], $§);
        }
    }

    return $data;
}

/**
 * Load block configuration
 */
function load_block(array $data, array $ext = []): array
{
    if (!empty($ext['vars'])) {
        $data['vars'] = empty($data['vars']) ? $ext['vars'] : array_replace($data['vars'], $ext['vars']);
    }

    unset($ext['vars']);

    return array_replace($data, $ext);
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

    return !$cfg['active'] || $cfg['priv'] && allowed($cfg['priv']) || account\get('admin') || in_array($key, account\get('priv'));
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
    if (($data = & registry('msg')) === null) {
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
 * Gets absolute path to specified subpath in given directory
 *
 * @throws DomainException
 */
function path(string $dir, string $id = null): string
{
    if (!$dir || empty(APP['path'][$dir])) {
        throw new DomainException(i18n('Invalid path'));
    }

    return APP['path'][$dir] . ($id && ($id = trim($id, '/')) ? '/' . $id : '');
}

/**
 * Gets absolute path to file from specified URL
 *
 * @throws DomainException
 */
function file(string $url): string
{
    if (!$url || !preg_match('#^' . APP['url.file'] . '(.+)#', $url, $match)) {
        throw new DomainException(i18n('Invalid path'));
    }

    return path('file', $match[1]);
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
 * Gets or adds layout block
 *
 * @throws DomainException
 */
function layout(string $id = null, array $§ = null): ?array
{
    if (($data = & registry('layout')) === null) {
        $data = [];
        $data = event(['layout'], $data);
    }

    if ($id === null) {
        return $data;
    }

    if (empty($data[$id]) && $§ === null) {
        return null;
    }

    if (empty($data[$id])) {
        if (empty($§['type']) || !($type = cfg('block', $§['type']))) {
            throw new DomainException(i18n('Invalid block %s', $id));
        }

        $data[$id] = arr\replace(APP['layout'], $type, $§, ['id' => $id]);
    } elseif ($§) {
        $data[$id] = load_block($data[$id], $§);
    }

    return $data[$id];
}

/**
 * Render block
 */
function §(string $id): string
{
    if (!($§ = layout($id)) || !$§['active'] || $§['priv'] && !allowed($§['priv'])) {
        return '';
    }

    $§ = event(['block.' . $§['type'], 'layout.' . $id], $§);
    $type = cfg('block', $§['type']);

    if (isset($type['vars'])) {
        $§['vars'] = arr\replace($type['vars'], $§['vars'] ?? []);
    }

    return $type['call']($§);
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
function url(string $path = '', array $param = [], bool $preserve = false): string
{
    $param = array_filter($preserve ? array_replace(request\get('param'), $param) : $param, 'is_scalar');

    return '/' . trim($path, '/') . ($param ? '?' . http_build_query($param, '', '&amp;') : '');
}

/**
 * GUI URL
 *
 * @see location /gui in nginx.conf for fallback paths
 */
function gui(string $path): string
{
    return APP['url.gui'] . get('gui') . '/' . trim($path, '/');
}

/**
 * cURL request
 *
 * @throws DomainException
 */
function curl(string $url, array $param = []): ?string
{
    if (!$url) {
        throw new DomainException(i18n('Invalid URL'));
    } elseif ($param) {
        $url .= '?' . http_build_query($param);
    }

    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_URL => $url] + cfg('app', 'curl'));
    $result = curl_exec($curl);
    curl_close($curl);

    return $result ?: null;
}
