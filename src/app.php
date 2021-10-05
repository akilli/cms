<?php
declare(strict_types=1);

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
    $events = arr\prefix(array_reverse($app['event']), 'response:');
    $data = arr\replace(APP['response'], event($events, APP['response']));

    if (!$app['valid']) {
        http_response_code(404);
    }

    foreach ($data['header'] as $key => $val) {
        header($key . ': ' . $val);
    }

    return $data['body'];
}

/**
 * Internal registry
 */
function &registry(string $id): ?array
{
    static $data = [];

    if (!array_key_exists($id, $data)) {
        $data[$id] = null;
    }

    return $data[$id];
}

/**
 * Returns preloaded configuration stored under given id, optionally just a part of it
 */
function cfg(string $id, string $key = null): mixed
{
    $cfg = registry('cfg')[$id] ?? [];

    if ($key === null) {
        return $cfg;
    }

    return $cfg[$key] ?? null;
}

/**
 * Returns runtime data stored under given id, optionally just a part of it
 */
function data(string $id, string $key = null): mixed
{
    if (($data = &registry('data')[$id]) === null) {
        $data = [];
        $data = event([id('data', $id)], $data);
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
        if (!$cfg = cfg('event', $event)) {
            continue;
        }

        foreach (array_keys($cfg) as $call) {
            $data = $call($data);
            $stop = $data['_stop'] ?? null;
            unset($data['_stop']);

            if ($stop === true) {
                break 2;
            }

            if ($stop === false) {
                break;
            }
        }
    }

    return $data;
}

/**
 * Check access
 */
function allowed(string $id): bool
{
    if (!($privilege = cfg('privilege', $id)) || !($account = data('account'))) {
        return false;
    }

    return in_array('_all_', $account['privilege'])
        || $privilege['use'] && allowed($privilege['use'])
        || !$privilege['use'] && in_array($id, $account['privilege']);
}

/**
 * Returns account if given credentials are valid and automatically rehashes password if needed
 */
function login(string $username, string $password): ?array
{
    $account = entity\one('account', crit: [['username', $username], ['active', true]]);

    if (!$account || !password_verify($password, $account['password'])) {
        return null;
    }

    if (password_needs_rehash($account['password'], PASSWORD_DEFAULT)) {
        $data = ['id' => $account['id'], 'password' => $password];
        entity\save('account', $data);
        $account['password'] = $data['password'];
    }

    return $account;
}

/**
 * Token
 */
function token(): string
{
    if (!$token = session\get('token')) {
        $token = str\uniq();
        session\save('token', $token);
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
function msg(string $msg = null): array
{
    if (($data = &registry('msg')) === null) {
        $data = session\get('msg') ?: [];
        session\delete('msg');
    }

    if ($msg === null) {
        $old = $data;
        $data = [];

        return $old;
    }

    if ($msg) {
        $data[$msg] = ($data[$msg] ?? 0) + 1;
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
 * Joins given parts to an identifier for privileges, events, etc
 */
function id(string|int ...$args): string
{
    return implode(':', $args);
}

/**
 * Generates an URL with given path and params, optionally preserves existing params
 */
function url(string $path = '', array $get = [], bool $preserve = false): string
{
    return '/' . $path . urlquery($get, $preserve);
}

/**
 * Generates the query part of an URL with given params, optionally preserves existing params
 */
function urlquery(array $get, bool $preserve = false): string
{
    if ($preserve) {
        $get += data('request', 'get');
    }

    return $get ? '?' . http_build_query($get) : '';
}

/**
 * Generates the action URL from given arguments
 */
function actionurl(string $entityId, string $action, int $id = null): string
{
    return '/' . $entityId . ':' . $action . ($id ? ':' . $id : '');
}

/**
 * Generates the URL to resized version for given image and dimensions
 */
function resizeurl(string $url, int $width, int $height = null): string
{
    return '/resize-' . $width . ($height ? 'x' . $height : '') . $url;
}

/**
 * Generates the URL to cropped version for given image and dimensions
 */
function cropurl(string $url, int $width, int $height = null): string
{
    return '/crop-' . $width . ($height ? 'x' . $height : '') . $url;
}

/**
 * Generates the URL for given asset file
 */
function asseturl(string $id): string
{
    return APP['url']['asset'] . '/' . $id;
}

/**
 * Generates the URL to given GUI file
 */
function guiurl(string $id): string
{
    return APP['url']['gui'] . '/' . $id;
}

/**
 * Generates the URL to given extension GUI file
 */
function exturl(string $id): string
{
    return APP['url']['ext'] . '/' . $id;
}

/**
 * Gets absolute path to given asset file or URL
 */
function assetpath(string $id): string
{
    return APP['path']['asset'] . '/' . preg_replace('#^' . APP['url']['asset'] . '/#', '', $id);
}

/**
 * Gets absolute path to specified extension GUI file or URL
 */
function guipath(string $id): string
{
    return APP['path']['app.gui'] . '/' . preg_replace('#^' . APP['url']['gui'] . '/#', '', $id);
}

/**
 * Gets absolute path to specified extension GUI file or URL
 */
function extpath(string $id): string
{
    return APP['path']['ext.gui'] . '/' . preg_replace('#^' . APP['url']['ext'] . '/#', '', $id);
}

/**
 * Renders template with given variables
 */
function tpl(string $tpl, array $var = []): string
{
    $ext = APP['path']['ext.tpl'] . '/' . $tpl;
    $var['tpl'] = is_file($ext) ? $ext : APP['path']['app.tpl'] . '/' . $tpl;

    if (!is_file($var['tpl'])) {
        return '';
    }

    unset($tpl);
    $var = fn(string $key): mixed => $var[$key] ?? null;
    ob_start();
    include $var('tpl');

    return ob_get_clean();
}

/**
 * Formats date and time
 */
function datetime(?string $val, string $format): ?string
{
    return $val ? datefmt_format(datefmt_create(null, 0, 0, null, null, $format), date_create($val)) : null;
}
