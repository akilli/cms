<?php
declare(strict_types=1);

namespace event;

use app;
use arr;
use contentfilter;
use entity;
use file;
use layout;
use request;
use session;
use str;
use DomainException;

function data_account(): array
{
    $id = (int) session\get('account');

    if ($id && ($data = entity\one('account', crit: [['id', $id]]))) {
        $data['privilege'] = entity\one('role', crit: [['id', $data['role_id']]])['privilege'];
        $data['privilege'][] = '_public_';
        $data['privilege'][] = '_user_';
    } else {
        $data = entity\item('account');
        $data['privilege'] = ['_public_', '_guest_'];
        session\delete('account');
    }

    return $data;
}

function data_app(array $data): array
{
    $data = arr\replace(APP['data']['app'], $data);
    $url = app\data('request', 'url');

    if (preg_match('#^/(?:|[a-z0-9_\-\./]+\.html)$#', $url, $match)
        && ($page = entity\one('page', crit: [['url', $url]], select: ['id', 'entity_id']))
        && ($data['page'] = entity\one($page['entity_id'], crit: [['id', $page['id']]]))
    ) {
        $data['type'] = 'html';
        $data['entity_id'] = $data['page']['entity_id'];
        $data['action'] = 'view';
        $data['id'] = $data['page']['id'];
        $data['entity'] = $data['page']['_entity'];
    } elseif (preg_match('#^/([a-z_]+)(?:|/([^/\.]+))\.json$#u', $url, $match)) {
        $data['type'] = 'json';
        $data['entity_id'] = $match[1];
        $data['action'] = isset($match[2]) ? 'view' : 'index';
        $data['id'] = $match[2] ?? null;
    } elseif (preg_match('#^/([a-z_]+)/([a-z_]+)(?:|/([^/\.]+))$#u', $url, $match)) {
        $data['type'] = 'html';
        $data['entity_id'] = $match[1];
        $data['action'] = $match[2];
        $data['id'] = $match[3] ?? null;
    }

    $data['entity'] = !$data['entity'] && $data['entity_id'] ? app\cfg('entity', $data['entity_id']) : $data['entity'];
    $data['parent_id'] = $data['entity']['parent_id'] ?? null;
    $privilege = app\cfg('privilege', app\id($data['entity_id'], $data['action']));
    $data['area'] = $privilege && $privilege['use'] === '_public_' ? '_public_' : '_admin_';
    $data['valid'] = $data['entity_id']
        && $data['action']
        && app\allowed(app\id($data['entity_id'], $data['action']))
        && $data['entity']
        && in_array($data['action'], $data['entity']['action'])
        && (!in_array($data['action'], ['delete', 'view']) || $data['id'] && entity\size($data['entity_id'], [['id', $data['id']]]))
        && ($data['action'] !== 'edit' || !$data['id'] || entity\size($data['entity_id'], [['id', $data['id']]]))
        && (!$data['page'] || !$data['page']['disabled']);

    if ($data['valid']) {
        $data['event'] = [
            $data['type'],
            app\id($data['type'], $data['area']),
            app\id($data['type'], $data['action']),
            ...($data['parent_id'] ? [app\id($data['type'], $data['parent_id'], $data['action'])] : []),
            app\id($data['type'], $data['entity_id'], $data['action']),
            ...($data['page'] ? [app\id($data['type'], 'page', $data['action'], $data['id'])] : []),
        ];
    } else {
        $data['event'] = [$data['type'], app\id($data['type'], '_invalid_')];
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
                'parent_id' => $item['parent_id'],
                'sort' => $item['sort'],
                'cfg' => ['attr' => ['id' => $item['entity_id'] . '-' . $item['block_id']], 'tag' => 'app-block'],
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
    $data['get'] = request\filter($_GET);

    if (!empty($_POST['token'])) {
        if (session\get('token') === $_POST['token']) {
            unset($_POST['token']);
            $data['file'] = array_filter(array_map('request\normalize', $_FILES));
            $data['post'] = $_POST;
            $data['post'] = array_replace_recursive($data['post'], request\convert($data['file']));
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
    foreach ($data['_entity']['unique'] as $attrIds) {
        $item = arr\replace(array_fill_keys($attrIds, null), $data['_old'], $data);
        $crit = [['id', $data['_old']['id'] ?? null, APP['op']['!=']]];
        $labels = [];

        foreach ($attrIds as $attrId) {
            $crit[] = [$attrId, $item[$attrId]];
            $labels[] = $data['_entity']['attr'][$attrId]['name'];
        }

        if (entity\size($data['_entity']['id'], $crit)) {
            foreach ($attrIds as $attrId) {
                $data['_error'][$attrId][] = app\i18n('Combination of %s must be unique', implode(', ', $labels));
            }
        }
    }

    return $data;
}

function entity_file_prevalidate(array $data): array
{
    if ($data['_entity']['id'] === 'iframe') {
        $data['mime'] = 'text/html';

        if ($data['_old'] && !empty($data['url']) && $data['url'] !== $data['_old']['url']) {
            $data['_error']['url'][] = app\i18n('URL must not change');
        }
    } elseif ($data['_entity']['attr']['url']['uploadable'] && !empty($data['url'])) {
        if (!$item = app\data('request', 'file')['url'] ?? null) {
            $data['_error']['url'][] = app\i18n('No upload file');
        } elseif ($data['_old'] && $item['type'] !== $data['_old']['mime']) {
            $data['_error']['url'][] = app\i18n('MIME-Type must not change');
        } elseif ($data['_old']) {
            $data['url'] = $data['_old']['url'];
        } else {
            $data['url'] = app\file($item['name']);
            $data['mime'] = $item['type'];

            if (entity\size('file', [[['url', $data['url']], ['thumb', $data['url']]]])) {
                $data['_error']['url'][] = app\i18n('Please change filename to generate an unique URL');
            }
        }
    }

    if (!empty($data['thumb']) && ($item = app\data('request', 'file')['thumb'] ?? null)) {
        $data['thumb'] = app\file($item['name']);
        $crit = array_merge(
            [[['url', $data['thumb']], ['thumb', $data['thumb']]]],
            $data['_old'] ? [['id', $data['_old']['id'], APP['op']['!=']]] : []
        );

        if (entity\size('file', $crit)) {
            $data['_error']['thumb'][] = app\i18n('Please change filename to generate an unique URL');
        }
    }

    return $data;
}

/**
 * @throws DomainException
 */
function entity_file_postsave(array $data): array
{
    $upload = function (string $attrId) use ($data): ?string {
        $item = app\data('request', 'file')[$attrId] ?? null;
        return $item && !file\upload($item['tmp_name'], app\filepath($data[$attrId])) ? $item['name'] : null;
    };

    if ($data['_entity']['attr']['url']['uploadable'] && !empty($data['url']) && ($name = $upload('url'))
        || !empty($data['thumb']) && ($name = $upload('thumb'))
    ) {
        throw new DomainException(app\i18n('Could not upload %s', $name));
    }

    if (array_key_exists('thumb', $data)
        && !$data['thumb']
        && $data['_old']['thumb']
        && !file\delete(app\filepath($data['_old']['thumb']))
    ) {
        throw new DomainException(app\i18n('Could not delete file'));
    }

    return $data;
}

/**
 * @throws DomainException
 */
function entity_file_postdelete(array $data): array
{
    if ($data['_entity']['attr']['url']['uploadable'] && !file\delete(app\filepath($data['_old']['url']))
        || $data['_old']['thumb'] && !file\delete(app\filepath($data['_old']['thumb']))
    ) {
        throw new DomainException(app\i18n('Could not delete file'));
    }

    return $data;
}

function entity_page_postvalidate_menu(array $data): array
{
    if ($data['_old']
        && !empty($data['parent_id'])
        && ($parent = entity\one('page', crit: [['id', $data['parent_id']]], select: ['path']))
        && in_array($data['_old']['id'], $parent['path'])
    ) {
        $data['_error']['parent_id'][] = app\i18n('Cannot assign the page itself or a subpage as parent');
    }

    return $data;
}

function entity_page_postvalidate_url(array $data): array
{
    $root = entity\one('page', crit: [['url', '/']], select: ['id']);
    $slug = $data['slug'] ?? $data['_old']['slug'] ?? null;
    $pId = array_key_exists('parent_id', $data) ? $data['parent_id'] : ($data['_old']['parent_id'] ?? null);
    $crit = [['slug', $slug], ['parent_id', [null, $root['id']]], ['id', $data['_old']['id'] ?? null, APP['op']['!=']]];

    if (($pId === null || $pId === $root['id']) && entity\size('page', $crit)) {
        $data['_error']['slug'][] = app\i18n('Please change slug to generate an unique URL');
    }

    return $data;
}

function entity_page_presave(array $data): array
{
    $data['account_id'] = app\data('account', 'id');

    return $data;
}

/**
 * @throws DomainException
 */
function entity_role_predelete(array $data): array
{
    if (entity\size('account', [['role_id', $data['id']]])) {
        throw new DomainException(app\i18n('Cannot delete used role'));
    }

    return $data;
}

function layout_postrender(array $data): array
{
    if ($data['image']) {
        $data['html'] = contentfilter\image($data['html'], $data['image']);
    }

    return $data;
}

function layout_postrender_body(array $data): array
{
    $data['html'] = contentfilter\block($data['html']);
    $data['html'] = contentfilter\email($data['html']);
    $data['html'] = contentfilter\tel($data['html']);
    $data['html'] = contentfilter\msg($data['html']);

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
    $data['redirect'] = app\action($app['entity_id'], 'index');
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
    $data['body'] = ($id = app\data('app', 'id')) ? layout\render_entity($id) : '';
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
            if ($attr['type'] === 'password') {
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
