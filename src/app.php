<?php
declare(strict_types = 1);

namespace app;

use account;
use entity;
use layout;
use request;
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

    $app = & registry('app');
    $app = APP['app'];
    $app['lang'] = locale_get_primary_language('');
    $app['gui'] = max(filemtime(path('gui')), file_exists(path('ext.gui')) ? filemtime(path('ext.gui')) : 0);
    $url = request\data('url');
    $pattern = '#^/(?P<entity_id>[a-z_]+)?(?:/(?P<action>[a-z_]+))?(?:/(?P<id>[^/]+))?(?P<invalid>.*)#u';
    $page = entity\one('page', [['url', $url]], ['select' => ['id', 'entity_id']]);

    if ($page && ($app['page'] = entity\one($page['entity_id'], [['id', $page['id']]]))) {
        $app['entity_id'] = $app['page']['entity_id'];
        $app['action'] = 'view';
        $app['id'] = $app['page']['id'];
        $app['entity'] = $app['page']['_entity'];
    } elseif (preg_match($pattern, $url, $match) && $match['entity_id'] && $match['action'] && !$match['invalid']) {
        $app['entity_id'] = $match['entity_id'];
        $app['action'] = $match['action'];
        $app['id'] = $match['id'] ?: null;
        $app['entity'] = cfg('entity', $match['entity_id']);
    } else {
        invalid();
        return;
    }

    // Gather request-data
    $app['parent_id'] = $app['entity']['parent_id'] ?? null;
    $app['area'] = empty(cfg('priv', $app['entity_id'] . '/' . $app['action'])['active']) ? '_public_' : '_admin_';
    $app['public'] = $app['area'] === '_public_';
    $blacklist = !$app['public'] && in_array(preg_replace('#^www\.#', '', request\data('host')), cfg('app', 'admin.blacklist'));
    $allowed = !$blacklist && allowed($app['entity_id'] . '/' . $app['action']);
    $ns = 'action\\';
    $real = is_callable($ns . $app['entity_id'] . '_' . $app['action']) ? $ns . $app['entity_id'] . '_' . $app['action'] : null;

    // Dispatch request
    if ($allowed && !$app['entity'] && $real) {
        $real();
    } elseif (!$allowed
        || !$app['entity']
        || !in_array($app['action'], $app['entity']['action'])
        || !$app['page'] && in_array($app['action'], ['delete', 'view']) && (!$app['id'] || !entity\size($app['entity_id'], [['id', $app['id']]]))
        || $app['public'] && (!$app['page'] || $app['page']['disabled'] || $app['page']['status'] !== 'published' && !allowed($app['entity_id'] . '/edit'))
    ) {
        invalid();
    } elseif ($real) {
        $real($app['entity']);
    } elseif ($app['parent_id'] && is_callable($ns . $app['parent_id'] . '_' . $app['action'])) {
        ($ns . $app['parent_id'] . '_' . $app['action'])($app['entity']);
    } elseif (is_callable($ns . $app['action'])) {
        ($ns . $app['action'])($app['entity']);
    }
}

/**
 * Handles invalid reuqests
 */
function invalid(): void
{
    http_response_code(404);
    $app = & registry('app');
    $app['invalid'] = true;
    $layout = & registry('layout');
    $layout = null;
}

/**
 * Returns response
 */
function response(): string
{
    return layout\block('root');
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
function data(string $id)
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

    if (in_array($id, ['attr', 'block', 'entity'])) {
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
        foreach ($cfg as $id => $block) {
            $data[$key][$id] = empty($data[$key][$id]) ? $block : load_block($data[$key][$id], $block);
        }
    }

    return $data;
}

/**
 * Load block configuration
 */
function load_block(array $data, array $ext = []): array
{
    if (!empty($ext['cfg'])) {
        $data['cfg'] = empty($data['cfg']) ? $ext['cfg'] : array_replace($data['cfg'], $ext['cfg']);
    }

    unset($ext['cfg']);

    return array_replace($data, $ext);
}

/**
 * Dispatches a group of events with given data
 *
 * Every listener can stop further propagation of current event or the whole group by setting the $data['_stop'] to
 * `false` for current event or `true` for the whole group
 */
function event(array $events, array $data): array
{
    unset($data['_stop']);

    foreach ($events as $event) {
        if (($cfg = cfg('event', $event)) && asort($cfg, SORT_NUMERIC)) {
            foreach (array_keys($cfg) as $call) {
                $data = $call($data);
                $stop = $data['_stop'] ?? null;
                unset($data['_stop']);

                if ($stop === true) {
                    break 2;
                } elseif ($stop === false) {
                    break;
                }
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

    return !$cfg['active'] || account\data('admin') || $cfg['priv'] && allowed($cfg['priv']) || !$cfg['priv'] && in_array($key, account\data('priv'));
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
function log($msg): void
{
    file_put_contents(APP['log'], '[' . date('r') . '] ' . $msg . "\n\n", FILE_APPEND);
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
 * Renders template with given variables
 */
function tpl(string $tpl, array $var = []): string
{
    $var['tpl'] = ($ext = path('ext.tpl', $tpl)) && is_file($ext) ? $ext : path('tpl', $tpl);

    if (!is_file($var['tpl'])) {
        return '';
    }

    unset($tpl);
    $var = function ($key) use ($var) {
        return $var[$key] ?? null;
    };
    ob_start();
    include $var('tpl');

    return ob_get_clean();
}

/**
 * Generates an HTML-element
 */
function html(string $tag, array $attrs = [], string $val = null): string
{
    $a = '';

    foreach ($attrs as $k => $v) {
        if ($v === false) {
            continue;
        } elseif ($v === true) {
            $v = $k;
        }

        $a .= ' ' . $k . '="' . addcslashes((string) $v, '"') . '"';
    }

    return in_array($tag, APP['html.void']) ? '<' . $tag . $a . ' />' : '<' . $tag . $a . '>' . $val . '</' . $tag . '>';
}

/**
 * Converts special chars to HTML entities
 */
function enc(?string $val): string
{
    return $val ? htmlspecialchars($val, ENT_QUOTES, ini_get('default_charset'), false) : '';
}

/**
 * Generates URL by given path and params, optionally preserves existing params
 */
function url(string $path = '', array $get = [], bool $preserve = false): string
{
    $get = $preserve ? $get + request\data('get') : $get;
    $query = $get ? http_build_query($get, '', '&amp;') : '';

    return '/' . trim($path, '/') . ($query ? '?' . $query : '');
}

/**
 * GUI URL
 *
 * @see location /gui in nginx.conf for fallback paths
 */
function gui(string $path): string
{
    return APP['url.gui'] . data('gui') . '/' . trim($path, '/');
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
    curl_setopt_array($curl, [CURLOPT_PROXY => cfg('app', 'proxy'), CURLOPT_URL => $url] + APP['curl']);
    $result = curl_exec($curl);
    curl_close($curl);

    return $result ?: null;
}

/**
 * Error
 */
function error(int $severity, string $msg, string $file, int $line): void
{
    log(new ErrorException($msg, 0, $severity, $file, $line));
}

/**
 * Exception
 */
function exception(Throwable $e): void
{
    log($e);
}

/**
 * Shutdown
 */
function shutdown(): void
{
    if ($data = registry('msg')) {
        session\set('msg', $data);
    }
}
