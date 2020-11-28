<?php
declare(strict_types=1);

namespace event\data;

use app;
use arr;
use entity;
use layout;
use request;
use session;
use str;

function account(): array
{
    $id = (int) session\get('account');

    if ($id && ($data = entity\one('account', [['id', $id]]))) {
        $data['priv'] = entity\one('role', [['id', $data['role_id']]])['priv'];
        $data['priv'][] = '_user_';
        $data['admin'] = in_array('_all_', $data['priv']);
    } else {
        $data = entity\item('account');
        $data['priv'] = ['_guest_'];
        $data['admin'] = false;
        session\delete('account');
    }

    return $data;
}

function app(array $data): array
{
    $data = arr\replace(APP['data']['app'], $data);
    $request = app\data('request');

    if (preg_match('#^/(?:|[a-z0-9_\-\./]+\.html)$#', $request['url'], $match)
        && ($page = entity\one('page', [['url', $request['url']]], select: ['id', 'entity_id']))
        && ($data['page'] = entity\one($page['entity_id'], [['id', $page['id']]]))
    ) {
        $data['entity_id'] = $data['page']['entity_id'];
        $data['action'] = 'view';
        $data['id'] = $data['page']['id'];
        $data['entity'] = $data['page']['_entity'];
    } elseif (preg_match('#^/([a-z_]+)/([a-z_]+)(?:|/([^/]+))$#u', $request['url'], $match)) {
        $data['entity_id'] = $match[1];
        $data['action'] = $match[2];
        $data['id'] = $match[3] ?? null;
        $data['entity'] = app\cfg('entity', $match[1]);
    }

    $data['parent_id'] = $data['entity']['parent_id'] ?? null;
    $public = empty(app\cfg('priv', $data['entity_id'] . ':' . $data['action'])['active']);
    $data['area'] = $public ? '_public_' : '_admin_';
    $data['invalid'] = !$data['entity_id']
        || !$data['action']
        || !app\allowed($data['entity_id'] . ':' . $data['action'])
        || $data['entity'] && !in_array($data['action'], $data['entity']['action'])
        || !$data['page']
            && in_array($data['action'], ['delete', 'view'])
            && (!$data['id'] || $data['entity'] && !entity\size($data['entity_id'], [['id', $data['id']]]))
        || $data['page'] && $data['page']['disabled']
        || $data['area'] === '_admin_'
            && in_array(preg_replace('#^www\.#', '', $request['host']), app\cfg('app', 'blacklist'));

    return $data;
}

function layout(array $data): array
{
    $cfg = app\cfg('layout');
    $app = app\data('app');
    $keys = ['_all_', $app['area']];

    if ($app['invalid']) {
        $keys[] = '_invalid_';
    } else {
        $entityId = $app['entity_id'];
        $action = $app['action'];
        $keys[] = $action;

        if ($parentId = $app['parent_id']) {
            $keys[] = $parentId . ':' . $action;
        }

        $keys[] = $entityId . ':' . $action;

        if ($action === 'view' && $app['id']) {
            $pageKey = 'page:view:' . $app['id'];
            $keys[] = $pageKey;

            if ($dbLayout = entity\all('layout', [['page_id', $app['id']]])) {
                $dbBlocks = [];

                foreach (arr\group($dbLayout, 'entity_id', 'block_id') as $eId => $ids) {
                    foreach (entity\all($eId, [['id', $ids]]) as $item) {
                        $dbBlocks[$item['id']] = $item;
                    }
                }

                foreach ($dbLayout as $id => $item) {
                    $cfg[$pageKey]['layout-' . $item['parent_id'] .'-' . $item['name']] = layout\db_cfg(
                        $dbBlocks[$item['block_id']],
                        ['parent_id' => $item['parent_id'], 'sort' => $item['sort']]
                    );
                }
            }
        }
    }

    foreach ($keys as $key) {
        if (!empty($cfg[$key])) {
            foreach ($cfg[$key] as $id => $block) {
                $block['id'] = $id;
                $data[$id] = empty($data[$id]) ? $block : arr\extend($data[$id], $block);
            }
        }
    }

    return array_map('layout\cfg', $data);
}

function request(array $data): array
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
