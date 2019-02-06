<?php
declare(strict_types = 1);

namespace event;

use app;
use arr;
use entity;
use file;
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
        $data[$id] = arr\replace(APP['block'], $type);

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

    foreach ($data as $entityId => $entity) {
        $entity = arr\replace(APP['entity'], $entity, ['id' => $entityId]);

        if (!$entity['name'] || !($entity['name'] = app\i18n($entity['name']))) {
            throw new DomainException(app\i18n('Invalid configuration'));
        } elseif (!$entity['db'] || !$entity['type'] && !($entity['type'] = app\cfg('db', $entity['db'])['type'] ?? null)) {
            throw new DomainException(app\i18n('Invalid configuration'));
        } elseif (!$entity['parent_id'] && (empty($entity['attr']['id']) || empty($entity['attr']['name']))) {
            throw new DomainException(app\i18n('Invalid configuration'));
        } elseif ($entity['parent_id'] && (empty($data[$entity['parent_id']]) || !empty($data[$entity['parent_id']]['parent_id']))) {
            throw new DomainException(app\i18n('Invalid configuration'));
        } elseif ($entity['parent_id']) {
            $entity['attr'] = array_replace_recursive($data[$entity['parent_id']]['attr'], $entity['attr']);
        }

        foreach ($entity['attr'] as $attrId => $attr) {
            if (empty($attr['name']) || empty($attr['type']) || empty($cfg[$attr['type']]) || $attr['type'] === 'entity' && empty($attr['ref'])) {
                throw new DomainException(app\i18n('Invalid configuration'));
            }

            $attr = arr\replace(APP['attr'], $cfg[$attr['type']], $attr, ['id' => $attrId, 'name' => app\i18n($attr['name'])]);

            if (!in_array($attr['backend'], APP['backend'])) {
                throw new DomainException(app\i18n('Invalid configuration'));
            }

            $entity['attr'][$attrId] = $attr;
        }

        $data[$entityId] = $entity;
    }

    return $data;
}

/**
 * I18n config
 */
function cfg_i18n(array $data): array
{
    return $data + app\load('i18n/' . app\get('lang'));
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
            $data[$id]['name'] = $entity['name'] . ' ' . app\i18n($action);
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

        $item = arr\replace(APP['toolbar'], $item, ['id' => $id, 'level' => 1]);
        $item['name'] = app\i18n($item['name']);
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
    $url = request\get('url');
    $keys = ['_all_', app\get('area')];

    if (app\get('invalid')) {
        $keys[] = '_invalid_';
    } else {
        $entityId = app\get('entity_id');
        $action = app\get('action');
        $keys[] = $action;

        if ($parentId = app\get('parent_id')) {
            $keys[] = $parentId . '/' . $action;
        }

        $keys[] = $entityId . '/' . $action;
        $keys[] = $url;

        if (($page = app\get('page')) && ($dbLayout = entity\all('layout', [['page_id', $page['id']]]))) {
            $ids = array_column($dbLayout, 'block_id');
            $base = entity\item('block');
            $dbBlocks = [];

            foreach (array_unique(array_column(entity\all('block', [['id', $ids]], ['select' => ['entity_id']]), 'entity_id')) as $eId) {
                foreach (entity\all($eId, [['id', $ids]]) as $item) {
                    $dbBlocks[$item['id']] = $item;
                }
            }

            foreach ($dbLayout as $id => $item) {
                $cfg[$url][APP['layout.db'] . $item['name']] = [
                    'type' => preg_replace('#^block_#', '', $dbBlocks[$item['block_id']]['entity_id']),
                    'parent_id' => $item['parent_id'],
                    'sort' => $item['sort'],
                    'cfg' => array_diff_key($dbBlocks[$item['block_id']], $base),
                ];
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
            $data['_error'][$attrId] = app\i18n('Invalid password');
        }
    }

    return $data;
}

/**
 * File entity validate
 */
function entity_prevalidate_file(array $data): array
{
    if (empty($data['url'])) {
        return $data;
    }

    $item = request\get('file')['url'] ?? null;

    if (!$item) {
        $data['_error']['url'] = app\i18n('No upload file');
        return $data;
    }

    $data['ext'] = pathinfo($data['url'], PATHINFO_EXTENSION);
    $data['mime'] = $item['type'];

    if ($data['_old'] && ($data['ext'] !== $data['_old']['ext'] || $data['mime'] !== $data['_old']['mime'])) {
        $data['_error']['url'] = app\i18n('Cannot change filetype anymore');
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
    $item = request\get('file')['url'] ?? null;

    if ($item && !file\upload($item['tmp_name'], app\path('file', $data['id'] . '.' . $data['ext']))) {
        throw new DomainException(app\i18n('File upload failed for %s', $item['name']));
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
    if (!file\delete(app\path('file', $data['id'] . '.' . $data['ext']))) {
        throw new DomainException(app\i18n('Could not delete file'));
    }

    return $data;
}

/**
 * Layout entity posvalidate
 */
function entity_postvalidate_layout(array $data): array
{
    $crit = [['name', $data['name']], ['page_id', $data['page_id']]];

    if (!empty($data['_old']['id'])) {
        $crit[] = ['id', $data['_old']['id'], APP['crit']['!=']];
    }

    if (entity\size('layout', $crit)) {
        $data['_error']['name'] = app\i18n('Value must be unique for each page');
        $data['_error']['page_id'] = app\i18n('Value must be unique for each page');
    }

    return $data;
}

/**
 * Page entity postvalidate
 */
function entity_postvalidate_page(array $data): array
{
    if (empty($data['parent_id'])) {
        return $data;
    }

    $parent = entity\one('page', [['id', $data['parent_id']]]);

    if ($data['_old'] && in_array($data['_old']['id'], $parent['path'])) {
        $data['_error']['parent_id'] = app\i18n('Cannot assign the page itself or a child page as parent');
    }

    if ($parent['status'] === 'archived' && (!$data['_old'] || $data['parent_id'] !== $data['_old']['parent_id'])) {
        $data['_error']['parent_id'] = app\i18n('Cannot assign archived page as parent');
    } elseif (in_array($parent['status'], ['draft', 'pending']) && !empty($data['status']) && $data['status'] !== 'draft') {
        $data['_error']['status'] = app\i18n('Status must be draft, because parent was not published yet');
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
