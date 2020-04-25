<?php
declare(strict_types = 1);

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

/**
 * Account data
 */
function data_account(): array
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
        session\set('account', null);
    }

    return $data;
}

/**
 * Application data
 */
function data_app(array $data): array
{
    $data = arr\replace(APP['data']['app'], $data);
    $request = app\data('request');

    if (preg_match('#^/(?:|[a-z0-9_\-\./]+\.html)$#', $request['url'], $match)
        && ($page = entity\one('page', [['url', $request['url']]], ['select' => ['id', 'entity_id']]))
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
    $data['area'] = empty(app\cfg('priv', $data['entity_id'] . '/' . $data['action'])['active']) ? '_public_' : '_admin_';
    $data['invalid'] = !$data['entity_id']
        || !$data['action']
        || !app\allowed($data['entity_id'] . '/' . $data['action'])
        || $data['entity'] && !in_array($data['action'], $data['entity']['action'])
        || !$data['page'] && in_array($data['action'], ['delete', 'view']) && (!$data['id'] || $data['entity'] && !entity\size($data['entity_id'], [['id', $data['id']]]))
        || $data['page'] && $data['page']['disabled']
        || $data['area'] === '_admin_' && in_array(preg_replace('#^www\.#', '', $request['host']), app\cfg('app', 'blacklist'));

    return $data;
}

/**
 * Layout data
 */
function data_layout(array $data): array
{
    $cfg = app\cfg('layout');
    $app = app\data('app');
    $url = app\data('request', 'url');
    $keys = ['_all_', $app['area']];

    if ($app['invalid']) {
        $keys[] = '_invalid_';
    } else {
        $entityId = $app['entity_id'];
        $action = $app['action'];
        $keys[] = $action;

        if ($parentId = $app['parent_id']) {
            $keys[] = $parentId . '/' . $action;
        }

        $keys[] = $entityId . '/' . $action;
        $keys[] = $url;

        if (($page = $app['page']) && ($dbLayout = entity\all('layout', [['page_id', $page['id']]]))) {
            $dbBlocks = [];

            foreach (arr\group($dbLayout, 'entity_id', 'block_id') as $eId => $ids) {
                foreach (entity\all($eId, [['id', $ids]]) as $item) {
                    $dbBlocks[$item['id']] = $item;
                }
            }

            foreach ($dbLayout as $id => $item) {
                $cfg[$url]['layout-' . $item['parent_id'] .'-' . $item['name']] = layout\db($dbBlocks[$item['block_id']], ['parent_id' => $item['parent_id'], 'sort' => $item['sort']]);
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

/**
 * Request data
 */
function data_request(array $data): array
{
    $data = arr\replace(APP['data']['request'], $data);
    $data['host'] = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'];
    $data['proto'] = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null) === 'https' || ($_SERVER['HTTPS'] ?? null === 'on') ? 'https' : 'http';
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

        session\set('token', null);
    }

    return $data;
}

/**
 * Entity postvalidate password
 */
function entity_postvalidate_password(array $data): array
{
    foreach (array_intersect_key($data, $data['_entity']['attr']) as $attrId => $val) {
        if ($data['_entity']['attr'][$attrId]['type'] === 'password' && $val && !($data[$attrId] = password_hash($val, PASSWORD_DEFAULT))) {
            $data['_error'][$attrId][] = app\i18n('Invalid password');
        }
    }

    return $data;
}

/**
 * Entity postvalidate unique
 */
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

/**
 * File entity prevalidate
 */
function entity_prevalidate_file(array $data): array
{
    if ($data['_entity']['id'] === 'file_iframe') {
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
        $crit = array_merge([[['url', $data['thumb']], ['thumb', $data['thumb']]]], $data['_old'] ? [['id', $data['_old']['id'], APP['op']['!=']]] : []);

        if (entity\size('file', $crit)) {
            $data['_error']['thumb'][] = app\i18n('Please change filename to generate an unique URL');
        }
    }

    return $data;
}

/**
 * File entity postsave
 *
 * @throws DomainException
 */
function entity_postsave_file(array $data): array
{
    $upload = fn(string $attrId): ?string => ($item = app\data('request', 'file')[$attrId] ?? null) && !file\upload($item['tmp_name'], app\filepath($data[$attrId])) ? $item['name'] : null;

    if ($data['_entity']['attr']['url']['uploadable'] && !empty($data['url']) && ($name = $upload('url')) || !empty($data['thumb']) && ($name = $upload('thumb'))) {
        throw new DomainException(app\i18n('Could not upload %s', $name));
    }

    if (array_key_exists('thumb', $data) && !$data['thumb'] && $data['_old']['thumb'] && !file\delete(app\filepath($data['_old']['thumb']))) {
        throw new DomainException(app\i18n('Could not delete file'));
    }

    return $data;
}

/**
 * File entity postdelete
 *
 * @throws DomainException
 */
function entity_postdelete_file(array $data): array
{
    if ($data['_entity']['attr']['url']['uploadable'] && !file\delete(app\filepath($data['_old']['url']))
        || $data['_old']['thumb'] && !file\delete(app\filepath($data['_old']['thumb']))
    ) {
        throw new DomainException(app\i18n('Could not delete file'));
    }

    return $data;
}

/**
 * Page entity postvalidate menu
 */
function entity_postvalidate_page_menu(array $data): array
{
    if ($data['_old']
        && !empty($data['parent_id'])
        && ($parent = entity\one('page', [['id', $data['parent_id']]], ['select' => ['path']]))
        && in_array($data['_old']['id'], $parent['path'])
    ) {
        $data['_error']['parent_id'][] = app\i18n('Cannot assign the page itself or a subpage as parent');
    }

    return $data;
}

/**
 * Page entity postvalidate URL
 */
function entity_postvalidate_page_url(array $data): array
{
    $root = entity\one('page', [['url', '/']], ['select' => ['id']]);
    $slug = $data['slug'] ?? $data['_old']['slug'] ?? null;
    $pId = array_key_exists('parent_id', $data) ? $data['parent_id'] : ($data['_old']['parent_id'] ?? null);
    $crit = [['slug', $slug], ['parent_id', [null, $root['id']]], ['id', $data['_old']['id'] ?? null, APP['op']['!=']]];

    if (($pId === null || $pId === $root['id']) && entity\size('page', $crit)) {
        $data['_error']['slug'][] = app\i18n('Please change slug to generate an unique URL');
    }

    return $data;
}

/**
 * Page entity presave
 */
function entity_presave_page(array $data): array
{
    $data['account_id'] = app\data('account', 'id');

    return $data;
}

/**
 * Role entity predelete
 *
 * @throws DomainException
 */
function entity_predelete_role(array $data): array
{
    if (entity\size('account', [['role_id', $data['id']]])) {
        throw new DomainException(app\i18n('Cannot delete used role'));
    }

    return $data;
}

/**
 * Response
 */
function response(array $data): array
{
    if (!$data['body'] && !$data['redirect']) {
        $data['body'] = layout\block('html');
    }

    return $data;
}

/**
 * Delete response
 */
function response_delete(array $data): array
{
    $app = app\data('app');
    entity\delete($app['entity_id'], [['id', $app['id']]]);
    $data['redirect'] = app\url($app['entity_id'] . '/admin');
    $data['_stop'] = true;

    return $data;
}

/**
 * Account logout response
 */
function response_account_logout(array $data): array
{
    session\regenerate();
    $data['redirect'] = app\url('account/login');
    $data['_stop'] = true;

    return $data;
}

/**
 * Block API response
 */
function response_block_api(array $data): array
{
    $data['body'] = ($id = app\data('app', 'id')) ? layout\db_block($id) : '';
    $data['_stop'] = true;

    return $data;
}

/**
 * Layout postrender
 */
function layout_postrender(array $data): array
{
    $data['html'] = contentfilter\block($data['html']);
    $data['html'] = contentfilter\email($data['html']);
    $data['html'] = contentfilter\msg($data['html']);

    if ($data['image']) {
        $data['html'] = contentfilter\image($data['html'], $data['image']);
    }

    return $data;
}
