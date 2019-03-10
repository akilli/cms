<?php
declare(strict_types = 1);

namespace event;

use account;
use app;
use arr;
use entity;
use file;
use layout;
use request;
use DomainException;

/**
 * Block config
 *
 * @throws DomainException
 */
function cfg_block(array $data): array
{
    foreach ($data as $id => $type) {
        $data[$id] = arr\replace(APP['block'], $type, ['id' => $id]);

        if (!is_callable($type['call'])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }
    }

    return $data;
}

/**
 * Entity config
 *
 * @throws DomainException
 */
function cfg_entity(array $data): array
{
    $cfg = app\cfg('attr');

    // Entities
    foreach ($data as $entityId => $entity) {
        $entity = arr\replace(APP['entity'], $entity, ['id' => $entityId]);

        if (!$entity['name']
            || !$entity['db']
            || !$entity['type'] && !($entity['type'] = app\cfg('db', $entity['db'])['type'] ?? null)
            || $entity['parent_id'] && (empty($data[$entity['parent_id']]) || !empty($data[$entity['parent_id']]['parent_id']))
            || !$entity['parent_id'] && !arr\has($entity['attr'], ['id', 'name'], true)
        ) {
            throw new DomainException(app\i18n('Invalid configuration'));
        } elseif ($entity['parent_id']) {
            $entity['attr'] = array_replace_recursive($data[$entity['parent_id']]['attr'], $entity['attr']);
        }

        $data[$entityId] = $entity;
    }

    // Attributes
    foreach ($data as $entityId => $entity) {
        foreach ($entity['attr'] as $attrId => $attr) {
            if (empty($attr['name'])
                || empty($attr['type'])
                || empty($cfg[$attr['type']])
                || in_array($attr['type'], ['entity', 'multientity']) && empty($attr['ref'])
                || !empty($attr['ref']) && (empty($data[$attr['ref']]['attr']['id']['type']) || empty($cfg[$data[$attr['ref']]['attr']['id']['type']]))
            ) {
                throw new DomainException(app\i18n('Invalid configuration'));
            }

            // Auto-determine type from reference ID attribute
            if (in_array($attr['type'], ['entity', 'multientity'])) {
                $attr['backend'] = $cfg[$data[$attr['ref']]['attr']['id']['type']]['backend'];
            }

            $attr = arr\replace(APP['attr'], $cfg[$attr['type']], $attr, ['id' => $attrId, 'name' => app\i18n($attr['name'])]);

            if (!in_array($attr['backend'], APP['backend'])
                || !$attr['frontend']
                || !is_callable($attr['frontend'])
                || $attr['filter'] && !is_callable($attr['filter'])
                || $attr['min'] > 0 && $attr['max'] > 0 && $attr['min'] > $attr['max']
            ) {
                throw new DomainException(app\i18n('Invalid configuration'));
            }

            $attr['filter'] = $attr['filter'] ?: $attr['frontend'];
            $attr['opt.frontend'] = $attr['opt.frontend'] ?: $attr['opt'];
            $attr['opt.filter'] = $attr['opt.filter'] ?: $attr['opt'];
            $attr['opt.validator'] = $attr['opt.validator'] ?: $attr['opt'];
            $attr['opt.viewer'] = $attr['opt.viewer'] ?: $attr['opt'];
            $entity['attr'][$attrId] = $attr;
        }

        $entity['name'] = app\i18n($entity['name']);
        $data[$entityId] = $entity;
    }

    return $data;
}

/**
 * I18n config
 */
function cfg_i18n(array $data): array
{
    return $data + app\load('i18n/' . app\data('lang'));
}

/**
 * Option config
 */
function cfg_opt(array $data): array
{
    foreach ($data as $key => $opt) {
        $data[$key] = array_map('app\i18n', $opt);
    }

    return $data;
}

/**
 * Privilege config
 */
function cfg_priv(array $data): array
{
    foreach ($data as $id => $item) {
        $item = arr\replace(APP['priv'], $item);
        $item['name'] = $item['name'] ? app\i18n($item['name']) : '';
        $data[$id] = $item;
    }

    foreach (app\cfg('entity') as $entity) {
        if (in_array('edit', $entity['action']) && in_array('page', [$entity['id'], $entity['parent_id']])) {
            $id = $entity['id'] . '-publish';
            $data[$id]['name'] = $entity['name'] . ' ' . app\i18n('Publish');
            $data[$id] = arr\replace(APP['priv'], $data[$id]);
        }

        foreach ($entity['action'] as $action) {
            $id = $entity['id'] . '/' . $action;
            $data[$id]['name'] = $entity['name'] . ' ' . app\i18n(ucfirst($action));
            $data[$id] = arr\replace(APP['priv'], $data[$id]);
        }
    }

    return $data;
}

/**
 * Toolbar config
 *
 * @throws DomainException
 */
function cfg_toolbar(array $data): array
{
    foreach ($data as $id => $item) {
        if (empty($item['name']) || !empty($item['parent_id']) && empty($data[$item['parent_id']])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }

        $item = arr\replace(APP['toolbar'], $item, ['id' => $id, 'name' => app\i18n($item['name']), 'level' => 1]);
        $item['url'] = $item['action'] ? app\url($item['action']) : $item['url'];
        $item['sort'] = str_pad((string) $item['sort'], 5, '0', STR_PAD_LEFT) . '-' . $id;

        if ($item['parent_id']) {
            $item['level'] = $data[$item['parent_id']]['level'] + 1;
            $item['sort'] = $data[$item['parent_id']]['sort'] . '/' . $item['sort'];
        }

        $data[$id] = $item;
    }

    return arr\order($data, ['sort' => 'asc']);
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
    $url = request\data('url');
    $keys = ['_all_', app\data('area')];

    if (app\data('invalid')) {
        $keys[] = '_invalid_';
    } else {
        $entityId = app\data('entity_id');
        $action = app\data('action');
        $keys[] = $action;

        if ($parentId = app\data('parent_id')) {
            $keys[] = $parentId . '/' . $action;
        }

        $keys[] = $entityId . '/' . $action;
        $keys[] = $url;

        if (($page = app\data('page')) && ($dbLayout = entity\all('layout', [['page_id', $page['id']]]))) {
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
                $data[$id] = empty($data[$id]) ? $block : app\load_block($data[$id], $block);
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
