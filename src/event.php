<?php
declare(strict_types = 1);

namespace event;

use account;
use app;
use arr;
use contentfilter;
use entity;
use file;
use layout;
use request;
use DomainException;

/**
 * Application data
 */
function data_app(array $data): array
{
    $data = arr\replace(APP['app'], $data);
    $url = request\data('url');

    if (preg_match('#^/(?:|[a-z0-9-_/\.]+\.html)$#', request\data('url'), $match)
        && ($page = entity\one('page', [['url', $url]], ['select' => ['id', 'entity_id']]))
        && ($data['page'] = entity\one($page['entity_id'], [['id', $page['id']]]))
    ) {
        $data['entity_id'] = $data['page']['entity_id'];
        $data['action'] = 'view';
        $data['id'] = $data['page']['id'];
        $data['entity'] = $data['page']['_entity'];
    } elseif (preg_match('#^/([a-z_]+)/([a-z_]+)(?:|/([^/]+))$#u', $url, $match)) {
        $data['entity_id'] = $match[1];
        $data['action'] = $match[2];
        $data['id'] = $match[3];
        $data['entity'] = app\cfg('entity', $match[1]);
    }

    $data['parent_id'] = $data['entity']['parent_id'] ?? null;
    $data['area'] = empty(app\cfg('priv', $data['entity_id'] . '/' . $data['action'])['active']) ? '_public_' : '_admin_';
    $data['public'] = $data['area'] === '_public_';
    $blacklist = !$data['public'] && in_array(preg_replace('#^www\.#', '', request\data('host')), app\cfg('app', 'admin.blacklist'));
    $data['allowed'] = !$blacklist && app\allowed($data['entity_id'] . '/' . $data['action']);

    return $data;
}

/**
 * Layout
 *
 * @throws DomainException
 */
function layout(array $data): array
{
    $cfg = app\cfg('layout');
    $type = app\cfg('block');
    $app = app\data('app');
    $url = request\data('url');
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

        if (($page = $app['page']) && ($dbLayout = entity\all('layout_page', [['page_id', $page['id']]]))) {
            $dbBlocks = [];

            foreach (arr\group($dbLayout, 'entity_id', 'block_id') as $eId => $ids) {
                foreach (entity\all($eId, [['id', $ids]]) as $item) {
                    $dbBlocks[$item['id']] = $item;
                }
            }

            foreach ($dbLayout as $id => $item) {
                $c = ['parent_id' => $item['parent_id'], 'sort' => $item['sort']];
                $cfg[$url][layout\db_id($item)] = layout\db($dbBlocks[$item['block_id']]) + $c;
            }
        }
    }

    foreach ($keys as $key) {
        if (!empty($cfg[$key])) {
            foreach ($cfg[$key] as $id => $block) {
                $data[$id] = empty($data[$id]) ? $block : arr\extend($data[$id], $block);
            }
        }
    }

    foreach ($data as $id => $block) {
        if (empty($block['type']) || empty($type[$block['type']])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }

        unset($block['call']);
        $data[$id] = arr\replace(APP['layout'], $type[$block['type']], $block, ['id' => $id]);
    }

    return $data;
}

/**
 * Layout postrender
 */
function layout_postrender(array $data): array
{
    if ($data['image']) {
        $data['html'] = contentfilter\image($data['html'], $data['image']);
    }

    return $data;
}

/**
 * Layout postrender root
 */
function layout_postrender_root(array $data): array
{
    $data['html'] = layout\db_replace($data['html']);
    $data['html'] = contentfilter\email($data['html']);

    return $data;
}

/**
 * Entity postvalidate
 */
function entity_postvalidate(array $data): array
{
    $attrs = $data['_entity']['attr'];

    foreach (array_intersect_key($data, $data['_entity']['attr']) as $attrId => $val) {
        if ($attrs[$attrId]['type'] === 'password' && $val && !($data[$attrId] = password_hash($val, PASSWORD_DEFAULT))) {
            $data['_error'][$attrId][] = app\i18n('Invalid password');
        }
    }

    return $data;
}

/**
 * File entity prevalidate
 */
function entity_prevalidate_file(array $data): array
{
    if ($data['_entity']['attr']['url']['uploadable'] && !empty($data['url'])) {
        if (!$item = request\file('url')) {
            $data['_error']['url'][] = app\i18n('No upload file');
        } else {
            $data['ext'] = pathinfo($data['url'], PATHINFO_EXTENSION);
            $data['mime'] = $item['type'];

            if ($data['_old'] && ($data['ext'] !== $data['_old']['ext'] || $data['mime'] !== $data['_old']['mime'])) {
                $data['_error']['url'][] = app\i18n('Cannot change filetype anymore');
            }
        }
    }

    if (!empty($data['thumb_url']) && ($item = request\file('thumb_url'))) {
        $data['thumb_ext'] = pathinfo($data['thumb_url'], PATHINFO_EXTENSION);
        $data['thumb_mime'] = $item['type'];
    }

    return $data;
}

/**
 * Page entity presave
 */
function entity_presave_page(array $data): array
{
    $data['account_id'] = account\data('id');

    return $data;
}

/**
 * File entity postsave
 *
 * @throws DomainException
 */
function entity_postsave_file(array $data): array
{
    $id = $data['id'] ?? $data['_old']['id'] ?? null;
    $uploadable = $data['_entity']['attr']['url']['uploadable'];

    if ($uploadable && ($item = request\file('url')) && (!$id || !file\upload($item['tmp_name'], app\path('file', $id . '.' . $data['ext'])))) {
        throw new DomainException(app\i18n('File upload failed for %s', $item['name']));
    } elseif (($item = request\file('thumb_url')) && (!$id || !file\upload($item['tmp_name'], app\path('file', $id . APP['file.thumb'] . $data['thumb_ext'])))) {
        throw new DomainException(app\i18n('File upload failed for %s', $item['name']));
    }

    if (array_key_exists('thumb_url', $data)
        && !$data['thumb_url']
        && $data['_old']['thumb_url']
        && !file\delete(app\path('file', $data['_old']['id'] . APP['file.thumb'] . $data['_old']['thumb_ext']))
    ) {
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
    if ($data['_entity']['attr']['url']['uploadable']
        && !file\delete(app\path('file', $data['_old']['id'] . '.' . $data['_old']['ext']))
        && !file\delete(app\path('file', $data['_old']['id'] . APP['file.thumb'] . $data['_old']['thumb_ext']))
    ) {
        throw new DomainException(app\i18n('Could not delete file'));
    }

    return $data;
}

/**
 * Layout entity posvalidate
 */
function entity_postvalidate_layout(array $data): array
{
    if (empty($data['name']) || empty($data['page_id']) || empty($data['parent_id'])) {
        return $data;
    }

    $crit = [['name', $data['name']], ['page_id', $data['page_id']], ['parent_id', $data['parent_id']]];

    if (!empty($data['_old']['id'])) {
        $crit[] = ['id', $data['_old']['id'], APP['op']['!=']];
    }

    if (entity\size('layout', $crit)) {
        $data['_error']['name'][] = app\i18n('Name must be unique for selected parent block and page');
    }

    return $data;
}

/**
 * Page entity postvalidate status
 */
function entity_postvalidate_page_status(array $data): array
{
    if (!empty($data['parent_id']) && ($parent = entity\one('page', [['id', $data['parent_id']]], ['select' => ['status']]))) {
        if ($parent['status'] === 'archived' && (!$data['_old'] || $data['parent_id'] !== $data['_old']['parent_id'])) {
            $data['_error']['parent_id'][] = app\i18n('Cannot assign archived page as parent');
        } elseif (in_array($parent['status'], ['draft', 'pending']) && !empty($data['status']) && $data['status'] !== 'draft') {
            $data['_error']['status'][] = app\i18n('Status must be draft, because parent was not published yet');
        }
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
    if ((!array_key_exists('slug', $data) || $data['_old'] && $data['slug'] === $data['_old']['slug'])
        && (!array_key_exists('parent_id', $data) || $data['_old'] && $data['parent_id'] === $data['_old']['parent_id'])
    ) {
        return $data;
    }

    if (array_key_exists('slug', $data)) {
        $slug = $data['slug'];
    } elseif (array_key_exists('slug', $data['_old'])) {
        $slug = $data['_old']['slug'];
    } else {
        $slug = null;
    }

    if (array_key_exists('parent_id', $data)) {
        $parentId = $data['parent_id'];
    } elseif (array_key_exists('parent_id', $data['_old'])) {
        $parentId = $data['_old']['parent_id'];
    } else {
        $parentId = null;
    }

    $root = entity\one('page', [['url', '/']], ['select' => ['id']]);

    if ($parentId === null || $parentId === $root['id']) {
        $parentId = [null, $root['id']];
    }

    $crit = [['slug', $slug], ['parent_id', $parentId]];

    if ($data['_old']) {
        $crit[] = ['id', $data['_old']['id'], APP['op']['!=']];
    }

    if (entity\size('page', $crit)) {
        $data['_error']['slug'][] = app\i18n('Please change slug to generate an unique URL');
    }

    return $data;
}

/**
 * Page entity load
 */
function entity_load_page(array $data): array
{
    if (array_key_exists('content', $data)) {
        $data['teaser'] = preg_match('#^(<p[^>]*>.*?</p>)#', trim($data['content']), $m) ? $m[1] : '';
    }

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
