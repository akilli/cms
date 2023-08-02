<?php
declare(strict_types=1);

namespace event;

use app;
use attr;
use arr;
use contentfilter;
use DomainException;
use entity;
use file;
use layout;
use request;
use session;
use str;

function data_account(): array
{
    $id = (int)session\get('account');

    if ($id && ($account = entity\one('account', crit: [['id', $id], ['active', true]]))) {
        $privilege = entity\one('role', crit: [['id', $account['role_id']]])['privilege'];
        $account['privilege'] = ['_public_', '_user_', ...$privilege];

        return $account;
    }

    session\delete('account');

    return array_replace(entity\item('account'), ['privilege' => ['_public_', '_guest_']]);
}

function data_app(array $data): array
{
    $url = $data['url'] ?: app\data('request', 'url');

    if ($jsonUrl = strstr($url, '.json', true)) {
        $data['type'] = 'json';
        $url = $jsonUrl;
    }

    if (preg_match('#^/([a-z][a-z_\.]+):([a-z]+)(?:|\:([^/\:\.]+))$#', $url, $match)) {
        $data['entity_id'] = $match[1];
        $data['action'] = $match[2];
        $data['item_id'] = !empty($match[3]) ? (int)$match[3] : null;
    } elseif ($item = entity\one('url', crit: [['name', $url]])) {
        $data['entity_id'] = $item['target_entity_id'];
        $data['action'] = 'view';
        $data['item_id'] = $item['target_id'];
    }

    $data['event'] = [$data['type'], app\id($data['type'], '_invalid_')];

    if (!$data['entity_id'] || !$data['action']) {
        return $data;
    }

    $data['id'] = app\id($data['entity_id'], $data['action'], ...($data['item_id'] ? [$data['item_id']] : []));
    $data['entity'] ??= app\cfg('entity', $data['entity_id']);
    $withId = in_array($data['action'], ['delete', 'edit', 'view']);
    $data['valid'] = app\allowed(app\id($data['entity_id'], $data['action']))
        && $data['entity']
        && in_array($data['action'], $data['entity']['action'])
        && ($withId && $data['item_id'] || !$withId && !$data['item_id'])
        && (!$data['item_id'] || ($data['item'] = entity\one($data['entity_id'], crit: [['id', $data['item_id']]])));

    if (!$data['valid']) {
        return $data;
    }

    $data['event'] = [
        $data['type'],
        app\id($data['type'], $data['action']),
        app\id($data['type'], $data['entity_id'], $data['action'])
    ];

    if ($data['item_id']) {
        $data['event'][] = app\id($data['type'], $data['entity_id'], $data['action'], $data['item_id']);
    }

    return $data;
}

function data_layout(array $data): array
{
    $cfg = app\cfg('layout');
    $app = app\data('app');

    foreach ($app['event'] as $event) {
        foreach (($cfg[$event] ?? []) as $id => $block) {
            $block['id'] = $id;
            $data[$id] = empty($data[$id]) ? $block : arr\extend($data[$id], $block);
        }
    }

    return array_map(layout\init(...), $data);
}

function data_request(array $data): array
{
    $data['host'] = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'];
    $data['method'] = strtolower($_SERVER['REQUEST_METHOD']);
    $https = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null) === 'https' || ($_SERVER['HTTPS'] ?? null) === 'on';
    $data['proto'] = $https ? 'https' : 'http';
    $data['base'] = $data['proto'] . '://' . $data['host'];
    $data['url'] = str\enc(strip_tags(urldecode(explode('?', $_SERVER['REQUEST_URI'])[0])));
    $data['id'] = $data['base'] . rtrim($data['url'], '/');
    $data['get'] = request\getfilter($_GET);

    if (!empty($_POST['token'])) {
        if (session\get('token') === $_POST['token']) {
            unset($_POST['token']);
            $data['file'] = array_filter(array_map(request\normalize(...), $_FILES));
            $data['post'] = array_replace_recursive(request\postfilter($_POST), request\convert($data['file']));
        }

        session\delete('token');
    }

    return $data;
}

function entity_prevalidate_uid(array $data): array
{
    if (!empty($data['name']) && (!$data['_old'] || $data['name'] !== $data['_old']['name'])) {
        $data['uid'] = str\uid($data['name']);
    }

    if (!empty($data['image'])) {
        $uid = $data['uid'] ?? $data['_old']['uid'] ?? null;

        if ($uid) {
            $ext = '.' . pathinfo($data['image'], PATHINFO_EXTENSION);
            $data['image'] = app\asseturl($data['_entity']['id'] . '/' . $uid . $ext);
        } else {
            $data['_error']['image'][] = app\i18n('Could not generate image URL');
        }
    }

    return $data;
}

function entity_prevalidate_url(array $data): array
{
    if (empty($data['url'])) {
        return $data;
    }

    $crit = [['name', $data['url']]];

    if ($data['_old']) {
        $entityId = $data['_old']['entity_id'] ?? $data['_entity']['id'] ;
        $id = $data['_old']['id'];
        $crit[] = [['target_entity_id', $entityId, APP['op']['!=']], ['target_id', $id, APP['op']['!=']]];
    }

    if (entity\size('url', crit: $crit)) {
        $data['_error']['url'][] = app\i18n('This URL is already in use');
    }

    return $data;
}

function entity_postvalidate_password(array $data): array
{
    foreach (array_intersect_key($data, $data['_entity']['attr']) as $attrId => $val) {
        if ($data['_entity']['attr'][$attrId]['type'] === 'password'
            && $val
            && !($data[$attrId] = password_hash($val, PASSWORD_DEFAULT))
        ) {
            $data['_error'][$attrId][] = app\i18n('Invalid password');
        }
    }

    return $data;
}

function entity_postvalidate_unique(array $data): array
{
    foreach ($data['_entity']['unique'] as $attrIds) {
        $item = arr\replace(array_fill_keys($attrIds, null), $data['_old'], $data);

        if (!arr\has($item, $attrIds, true)) {
            continue;
        }

        $entityId = $data['_entity']['id'];
        $crit = $data['_old'] ? [['id', $data['_old']['id'], APP['op']['!=']]] : [];
        $labels = [];

        foreach ($attrIds as $attrId) {
            $crit[] = [$attrId, $item[$attrId]];
            $labels[] = $data['_entity']['attr'][$attrId]['name'];
        }

        if (entity\size($entityId, crit: $crit)) {
            foreach ($attrIds as $attrId) {
                $data['_error'][$attrId][] = app\i18n('Combination of %s must be unique', implode(', ', $labels));
            }
        }
    }

    return $data;
}

function entity_prevalidate_uploadable(array $data): array
{
    foreach ($data['_entity']['attr'] as $attrId => $attr) {
        if (!$attr['uploadable'] || empty($data[$attrId]) && (!$attr['required'] || attr\ignorable($data, $attr))) {
            continue;
        } elseif (!$item = app\data('request', 'file')[$attrId] ?? null) {
            $data['_error'][$attrId][] = app\i18n('No valid upload file');
        } elseif (!in_array($item['type'], $attr['accept'])) {
            $data['_error'][$attrId][] = app\i18n('Invalid file type');
        } else {
            $data[$attrId] = app\asseturl($data['_entity']['id'] . '/' . $item['name']);
        }
    }

    return $data;
}

/**
 * @throws DomainException
 */
function entity_postsave_uploadable(array $data): array
{
    $upload = function (string $attrId) use ($data): ?string {
        $item = app\data('request', 'file')[$attrId] ?? null;
        $path = app\assetpath($data[$attrId]);

        return $item && !file\upload($item['tmp_name'], $path) ? $item['name'] : null;
    };

    foreach ($data['_entity']['attr'] as $attrId => $attr) {
        if (!$attr['uploadable']) {
            continue;
        } elseif (!empty($data[$attrId]) && ($name = $upload($attrId))) {
            throw new DomainException(app\i18n('Could not upload %s', $name));
        } elseif (array_key_exists($attrId, $data)
            && !empty($data['_old'][$attrId])
            && $data['_old'][$attrId] !== $data[$attrId]
            && !file\delete(app\assetpath($data['_old'][$attrId]))
        ) {
            throw new DomainException(app\i18n('Could not delete %s', $data['_old'][$attrId]));
        }
    }

    return $data;
}

/**
 * @throws DomainException
 */
function entity_postdelete_uploadable(array $data): array
{
    foreach ($data['_entity']['attr'] as $attrId => $attr) {
        if ($attr['uploadable'] && $data['_old'][$attrId] && !file\delete(app\assetpath($data['_old'][$attrId]))) {
            throw new DomainException(app\i18n('Could not delete %s', $data['_old'][$attrId]));
        }
    }

    return $data;
}

function entity_file_presave(array $data): array
{
    if ($data['_entity']['attr']['name']['uploadable'] && !empty($data['name'])) {
        $data['mime'] = app\data('request', 'file')['name']['type'];
    }

    return $data;
}

function entity_menu_postvalidate(array $data): array
{
    if ($data['_old']
        && !empty($data['parent_id'])
        && ($parent = entity\one('menu', crit: [['id', $data['parent_id']]], select: ['path']))
        && in_array($data['_old']['id'], $parent['path'])
    ) {
        $data['_error']['parent_id'][] = app\i18n('Invalid parent menu item');
    }

    return $data;
}

/**
 * @throws DomainException
 */
function entity_role_predelete(array $data): array
{
    if (entity\size('account', crit: [['role_id', $data['id']]])) {
        throw new DomainException(app\i18n('Cannot delete used role'));
    }

    return $data;
}

function layout_postrender(array $data): array
{
    $view = app\data('app', 'action') === 'view';

    if ($data['image'] || $data['id'] === 'html') {
        $data['html'] = contentfilter\entity($data['html']);
        $data['html'] = contentfilter\file($data['html']);
        $data['html'] = contentfilter\msg($data['html']);
    }

    if ($view && $data['id'] === 'html') {
        $data['html'] = contentfilter\email($data['html']);
        $data['html'] = contentfilter\tel($data['html']);
    }

    if ($data['image']) {
        $data['html'] = contentfilter\image($data['html'], $data['image']);
    }

    if ($view && $data['id'] === 'html' || !$view && $data['id'] === 'head') {
        $data['html'] = contentfilter\asset($data['html']);
    }

    return $data;
}

function response_html(array $data): array
{
    if (!$data['body'] && empty($data['header']['location'])) {
        $data['body'] = layout\render_id('html');
    }

    return $data;
}

function response_html_delete(array $data): array
{
    $app = app\data('app');
    entity\delete($app['entity_id'], [['id', $app['item_id']]]);
    $data['header']['location'] = app\actionurl($app['entity_id'], 'index');
    $data['_stop'] = true;

    return $data;
}

function response_html_account_logout(array $data): array
{
    session\regenerate();
    $data['header']['location'] = app\url();
    $data['_stop'] = true;

    return $data;
}

function response_json_delete(array $data): array
{
    $app = app\data('app');
    $success = entity\delete($app['entity_id'], [['id', $app['item_id']]]);
    $data['body'] = json_encode(['id' => $app['item_id'], 'success' => $success, 'msg' => app\msg()]);
    $data['_stop'] = true;

    return $data;
}

function response_json_save(array $data): array
{
    $app = app\data('app');
    $item = app\data('request', 'post');

    if ($app['item_id']) {
        $item = ['id' => $app['item_id']] + $item;
    }

    $success = entity\save($app['entity_id'], $item);
    $id = $item['id'] ?? null;
    $error = $item['_error'] ?? null;
    $data['body'] = json_encode(['id' => $id, 'success' => $success, 'msg' => app\msg(), 'error' => $error]);
    $data['_stop'] = true;

    return $data;
}

function response_json_index(array $data): array
{
    $app = app\data('app');
    $attrIds = array_keys(arr\filter($app['entity']['attr'], 'viewer', null));
    $all = entity\all($app['entity_id']);

    foreach ($all as $id => $item) {
        $item = entity\uninit($item);
        $all[$id] = $attrIds ? arr\remove($item, $attrIds) : $item;
    }

    $data['body'] = json_encode($all);
    $data['_stop'] = true;

    return $data;
}

function response_json_view(array $data): array
{
    $app = app\data('app');
    $attrIds = array_keys(arr\filter($app['entity']['attr'], 'viewer', null));
    $item = entity\uninit($app['item']);
    $data['body'] = json_encode($attrIds ? arr\remove($item, $attrIds) : $item);
    $data['_stop'] = true;

    return $data;
}
