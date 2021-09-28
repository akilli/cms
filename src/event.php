<?php
declare(strict_types=1);

namespace event;

use app;
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

    if ($id && ($data = entity\one('account', crit: [['id', $id], ['active', true]]))) {
        $privilege = entity\one('role', crit: [['id', $data['role_id']]])['privilege'];
        $data['privilege'] = ['_public_', '_user_', ...$privilege];

        return $data;
    }

    session\delete('account');

    return array_replace(entity\item('account'), ['privilege' => ['_public_', '_guest_']]);
}

function data_app(array $data): array
{
    $data = arr\replace(APP['data']['app'], $data);
    $url = app\data('request', 'url');

    if (preg_match('#^/:([a-z_\.]+)(?:|/([^/\.]+))\.json$#u', $url, $match)) {
        $data['type'] = 'json';
        $data['entity_id'] = $match[1];
        $data['action'] = isset($match[2]) ? 'view' : 'index';
        $data['id'] = $match[2] ?? null;
    } elseif (preg_match('#^/:([a-z_\.]+)/([a-z_]+)(?:|/([^/\.]+))$#u', $url, $match)) {
        $data['entity_id'] = $match[1];
        $data['action'] = $match[2];
        $data['id'] = $match[3] ?? null;
    } elseif (preg_match('#^/~([a-z0-9\-]+)$#u', $url, $match)) {
        $data['entity_id'] = 'account';
        $data['action'] = 'view';
        $data['id'] = entity\one('account', crit: [['url', $url]], select: ['id'])['id'] ?? null;
    } elseif (($page = entity\one('page', crit: [['url', $url]], select: ['id', 'entity_id']))
        && ($data['page'] = entity\one($page['entity_id'], crit: [['id', $page['id']]]))
    ) {
        $data['entity_id'] = $data['page']['entity_id'];
        $data['action'] = 'view';
        $data['id'] = $data['page']['id'];
        $data['entity'] = $data['page']['_entity'];
    }

    $data['event'] = [$data['type'], app\id($data['type'], '_invalid_')];

    if (!$data['entity_id'] || !$data['action']) {
        return $data;
    }

    $data['entity'] ??= app\cfg('entity', $data['entity_id']);
    $data['parent_id'] = $data['entity']['parent_id'] ?? null;
    $privilege = app\cfg('privilege', app\id($data['entity_id'], $data['action']));
    $data['area'] = $privilege && $privilege['use'] === '_public_' ? '_public_' : '_admin_';
    $data['valid'] = app\allowed(app\id($data['entity_id'], $data['action']))
        && $data['entity']
        && in_array($data['action'], $data['entity']['action'])
        && (!$data['id'] || entity\size($data['entity_id'], crit: [['id', $data['id']]]))
        && (!in_array($data['action'], ['delete', 'view']) || $data['id']);

    if ($data['valid']) {
        $data['event'] = [
            $data['type'],
            app\id($data['type'], $data['area']),
            app\id($data['type'], $data['action']),
            ...($data['parent_id'] ? [app\id($data['type'], $data['parent_id'], $data['action'])] : []),
            app\id($data['type'], $data['entity_id'], $data['action']),
            ...($data['page'] ? [app\id($data['type'], 'page', $data['action'], $data['id'])] : []),
        ];
    }

    return $data;
}

function data_layout(array $data): array
{
    $cfg = app\cfg('layout');
    $app = app\data('app');

    if ($app['page']) {
        foreach (entity\all('layout', crit: [['page_id', $app['id']]]) as $item) {
            $cfg[app\id('html', 'page', 'view', $app['id'])]['layout-' . $item['id']] = [
                'type' => 'tag',
                'tag' => 'app-block',
                'parent_id' => $item['parent_id'],
                'sort' => $item['sort'],
                'cfg' => ['attr' => ['id' => $item['block_entity_id'] . '-' . $item['block_id']]],
            ];
        }
    }

    foreach ($app['event'] as $event) {
        foreach (($cfg[$event] ?? []) as $id => $block) {
            $block['id'] = $id;
            $data[$id] = empty($data[$id]) ? $block : arr\extend($data[$id], $block);
        }
    }

    return array_map('layout\block', $data);
}

function data_request(array $data): array
{
    $data = arr\replace(APP['data']['request'], $data);
    $data['host'] = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'];
    $data['method'] = strtolower($_SERVER['REQUEST_METHOD']);
    $https = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null) === 'https' || ($_SERVER['HTTPS'] ?? null) === 'on';
    $data['proto'] = $https ? 'https' : 'http';
    $data['base'] = $data['proto'] . '://' . $data['host'];
    $data['url'] = str\enc(strip_tags(urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))));
    $data['full'] = $data['base'] . rtrim($data['url'], '/');
    $data['get'] = request\getfilter($_GET);

    if (!empty($_POST['token'])) {
        if (session\get('token') === $_POST['token']) {
            unset($_POST['token']);
            $data['file'] = array_filter(array_map('request\normalize', $_FILES));
            $data['post'] = array_replace_recursive(request\postfilter($_POST), request\convert($data['file']));
        }

        session\delete('token');
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
    $parent = $data['_entity']['parent_id'] ? app\cfg('entity', $data['_entity']['parent_id']) : null;

    foreach ($data['_entity']['unique'] as $attrIds) {
        $item = arr\replace(array_fill_keys($attrIds, null), $data['_old'], $data);

        if (!arr\has($item, $attrIds, true)) {
            continue;
        }

        $entityId = $parent && arr\has($parent['attr'], $attrIds) ? $parent['id'] : $data['_entity']['id'];
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
        if (!$attr['uploadable'] || empty($data[$attrId])) {
            continue;
        } elseif (!$item = app\data('request', 'file')[$attrId] ?? null) {
            $data['_error'][$attrId][] = app\i18n('No upload file');
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
            && $data['_old'][$attrId]
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

function entity_account_prevalidate(array $data): array
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
            $data['_error']['image'][] = app\i18n('Could not generate profile image URL');
        }
    }

    return $data;
}

function entity_file_prevalidate(array $data): array
{
    if ($data['_entity']['attr']['name']['uploadable'] && !empty($data['name'])) {
        $mime = app\data('request', 'file')['name']['type'];

        if ($data['_old'] && $mime !== $data['_old']['mime']) {
            $data['_error']['name'][] = app\i18n('MIME-Type must not change');
        } elseif ($data['_old']) {
            $data['name'] = $data['_old']['name'];
        } elseif (entity\size($data['_entity']['id'], crit: [['thumb', $data['name']]])) {
            $data['_error']['name'][] = app\i18n('This filename is already in use');
        } else {
            $data['mime'] = $mime;
        }
    }

    if (!empty($data['thumb']) && !empty($data['name']) && $data['thumb'] === $data['name']
        || !empty($data['thumb']) && entity\size($data['_entity']['id'], crit: [['name', $data['thumb']]])
    ) {
        $data['_error']['thumb'][] = app\i18n('This filename is already in use');
    }

    return $data;
}

function entity_iframe_prevalidate(array $data): array
{
    if (!empty($data['name']) && $data['_old'] && $data['name'] !== $data['_old']['name']) {
        $data['_error']['name'][] = app\i18n('URL must not change');
    }

    return $data;
}

function entity_iframe_presave(array $data): array
{
    $data['mime'] = 'text/html';

    return $data;
}

function entity_menu_postvalidate(array $data): array
{
    if ($data['_old']
        && !empty($data['parent_id'])
        && ($parent = entity\one('menu', crit: [['id', $data['parent_id']]], select: ['path']))
        && in_array($data['_old']['id'], $parent['path'])
    ) {
        $data['_error']['parent_id'][] = app\i18n('Invalid parent item');
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
    if (app\data('app', 'area') === '_public_' && $data['image']) {
        $data['html'] = contentfilter\block($data['html']);
        $data['html'] = contentfilter\msg($data['html']);
        $data['html'] = contentfilter\image($data['html'], $data['image']);
    }

    return $data;
}

function layout_postrender_body(array $data): array
{
    $data['html'] = contentfilter\block($data['html']);
    $data['html'] = contentfilter\msg($data['html']);

    if (app\data('app', 'area') === '_public_') {
        $data['html'] = contentfilter\email($data['html']);
        $data['html'] = contentfilter\tel($data['html']);
    }

    return $data;
}

function layout_postrender_html(array $data): array
{
    if (app\data('app', 'area') === '_public_') {
        $data['html'] = contentfilter\asset($data['html']);
    }

    return $data;
}

function response_html(array $data): array
{
    if (!$data['body'] && !$data['redirect']) {
        $data['body'] = layout\render_id('html');
    }

    return $data;
}

function response_html_delete(array $data): array
{
    $app = app\data('app');
    entity\delete($app['entity_id'], [['id', $app['id']]]);
    $data['redirect'] = app\actionurl($app['entity_id'], 'index');
    $data['_stop'] = true;

    return $data;
}

function response_html_account_logout(array $data): array
{
    session\regenerate();
    $data['redirect'] = app\url();
    $data['_stop'] = true;

    return $data;
}

function response_html_block_api(array $data): array
{
    $id = app\data('app', 'id');
    $data['body'] = '';

    if (preg_match('#^([a-z_\.]+)-(\d+)$#', $id, $match)) {
        $data['body'] = layout\render_entity($match[1], (int)$match[2]);
    }

    $data['_stop'] = true;

    return $data;
}

function response_json(array $data): array
{
    header('content-type: application/json');
    $app = app\data('app');

    if (!$app['valid']) {
        return $data;
    }

    $filter = function (array $item): array {
        foreach ($item['_entity']['attr'] as $attrId => $attr) {
            if (!$attr['viewer']) {
                unset($item[$attrId]);
            }
        }

        return entity\uninit($item);
    };

    if ($app['id']) {
        $result = $filter(entity\one($app['entity_id'], crit: [['id', $app['id']]]));
    } else {
        $result = array_map($filter, entity\all($app['entity_id']));
    }

    $data['body'] = json_encode($result);

    return $data;
}
