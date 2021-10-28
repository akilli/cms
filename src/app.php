<?php
declare(strict_types=1);

namespace app;

use arr;
use entity;
use response;
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

    if (!empty(APP['type'][$app['type']])) {
        $data['header']['content-type'] = APP['type'][$app['type']];
    }

    return response\send($data['body'], $data['header'], $app['valid'] ? null : 404);
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
        $base = APP['data'][$id] ?? [];
        $data = $base;
        $data = event([id('data', $id)], $data);
        $data = $base ? arr\replace(APP['data'][$id], $data) : $data;
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
function event(array $keys, array $data): array
{
    unset($data['_stop']);
    $cfg = cfg('event');

    foreach ($keys as $key) {
        if (empty($cfg[$key])) {
            continue;
        }

        foreach ($cfg[$key] as $item) {
            $data = $item['call']($data);
            $stop = $data['_stop'] ?? null;
            unset($data['_stop']);

            if ($stop === true) {
                break 2;
            } elseif ($stop === false) {
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
    $call = function(string $key) use (&$call): ?string {
        if (!$privilege = cfg('privilege', $key)) {
            return null;
        }

        return $privilege['use'] ? $call($privilege['use']) : $key;
    };

    return ($id = $call($id))
        && ($ids = data('account', 'privilege'))
        && array_intersect(['_all_', $id], $ids)
        && (!in_array($id, ['_guest_', '_user_']) || in_array($id, $ids));
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
function i18n(string $key, string|int|float ...$args): string
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
 * Searches for custom placeholder elements, i. e. `<{custom-tag} id="{entity_id}-{id}"></{custom-tag}>`, and returns an
 * array of referenced IDs grouped by entities, i. e. ['{entity_id}' => [{id}, ...], ...]
 */
function placeholder(string $html, string $tag, array $entityIds = null): array
{
    $data = [];
    $entityPattern = $entityIds ? implode('|', $entityIds) : '[a-z][a-z_\.]*';
    $pattern = sprintf('#<%1$s id="(%2$s)-(\d+)">(?:[^<]*)</%1$s>#s', $tag, $entityPattern);

    if (preg_match_all($pattern, $html, $match)) {
        foreach ($match[1] as $key => $entityId) {
            if (!in_array($match[2][$key], $data[$entityId] ?? [])) {
                $data[$entityId][] = $match[2][$key];
            }
        }
    }

    return $data;
}

/**
 * Formats date and time
 */
function datetime(?string $val, string $format): ?string
{
    return $val ? datefmt_format(datefmt_create(null, 0, 0, null, null, $format), date_create($val)) : null;
}
