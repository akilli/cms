<?php
declare(strict_types = 1);

namespace app;

use arr;
use entity;
use request;
use session;
use str;
use Stringable;

/**
 * Runs application
 */
function run(): string
{
    $app = data('app');
    $ev = ['response'];

    if ($app['invalid']) {
        http_response_code(404);
    } else {
        array_unshift($ev, 'response.' . $app['action']);

        if ($app['parent_id']) {
            array_unshift($ev, 'response.' . $app['parent_id'] . '.' . $app['action']);
        }

        array_unshift($ev, 'response.' . $app['entity_id'] . '.' . $app['action']);
    }

    $data = arr\replace(APP['response'], event($ev, APP['response']));

    if ($data['redirect']) {
        request\redirect($data['redirect']);
        return '';
    }

    return $data['body'];
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
 */
function data(string $id, string $key = null): mixed
{
    if (($data = & registry('data.' . $id)) === null) {
        $data = [];
        $data = event(['data.' . $id], $data);
    }

    if ($key === null) {
        return $data;
    }

    return $data[$key] ?? null;
}

/**
 * Loads and returns configuration data
 */
function cfg(string $id, string $key = null): mixed
{
    // Workaround for config preloading
    if (defined('CFG')) {
        $data = CFG[$id] ?? [];
    } else {
        $data = registry('cfg.' . $id) ?? [];
    }

    if ($key === null) {
        return $data;
    }

    return $data[$key] ?? null;
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

    $data = data('account');

    return !$cfg['active'] || $data['admin'] || $cfg['priv'] && allowed($cfg['priv']) || !$cfg['priv'] && in_array($key, $data['priv']);
}

/**
 * Returns account if given credentials are valid and automatically rehashes password if needed
 */
function login(string $username, string $password): ?array
{
    $data = entity\one('account', [['username', $username]]);

    if (!$data || !password_verify($password, $data['password'])) {
        return null;
    }

    if (password_needs_rehash($data['password'], PASSWORD_DEFAULT)) {
        $acc = ['id' => $data['id'], 'password' => $password];
        entity\save('account', $acc);
        $data['password'] = $acc['password'];
    }

    return $data;
}

/**
 * Token
 */
function token(): string
{
    if (!$token = session\get('token')) {
        $token = str\uniq();
        session\set('token', $token);
    }

    return $token;
}

/**
 * Translate
 */
function i18n(string $key, string ...$args): string
{
    $key = cfg('i18n', APP['lang'])[$key] ?? $key;

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
function log(string|Stringable $msg): void
{
    file_put_contents(APP['log'], '[' . date('r') . '] ' . $msg . "\n\n", FILE_APPEND);
}

/**
 * Generates URL by given path and params, optionally preserves existing params
 */
function url(string $path = '', array $get = [], bool $preserve = false): string
{
    return '/' . $path . query($get, $preserve);
}

/**
 * Generates query part of an URL with given params, optionally preserves existing params
 */
function query(array $get, bool $preserve = false): string
{
    $get += $preserve ? data('request', 'get') : [];
    $query = $get ? http_build_query($get, '', '&amp;') : '';

    return $query ? '?' . $query : '';
}

/**
 * File URL
 */
function file(string $path = ''): string
{
    return APP['url']['file'] . '/' . $path;
}

/**
 * GUI URL
 */
function gui(string $path = ''): string
{
    return APP['url']['gui'] . '/' . APP['mtime'] . '/' . $path;
}

/**
 * Extension GUI URL
 */
function ext(string $path = ''): string
{
    return APP['url']['ext'] . '/' . APP['mtime'] . '/' . $path;
}

/**
 * Gets absolute path to specified file name or URL
 */
function filepath(string $id): string
{
    return APP['path']['file'] . '/' . basename($id);
}

/**
 * Renders template with given variables
 */
function tpl(string $tpl, array $var = []): string
{
    $ext = APP['path']['ext.tpl'] . '/' . $tpl;
    $var['tpl'] = is_file($ext) ? $ext : APP['path']['tpl'] . '/' . $tpl;

    if (!is_file($var['tpl'])) {
        return '';
    }

    unset($tpl);
    $var = fn($key) => $var[$key] ?? null;
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
