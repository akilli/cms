<?php
declare(strict_types = 1);

namespace cms;

use RuntimeException;

/**
 * App config listener
 */
function listener_cfg_app(array $data): array
{
    ini_set('default_charset', $data['charset']);
    ini_set('intl.default_locale', $data['locale']);
    ini_set('date.timezone', $data['timezone']);

    return $data;
}

/**
 * Entity config listener
 */
function listener_cfg_entity(array $data): array
{
    $model = cfg('model');
    $cfg = cfg('attr');

    foreach ($data as $eId => $entity) {
        $entity = array_replace(ENTITY, $entity);

        if (!$entity['name'] || !$entity['model'] || empty($model[$entity['model']]) || !$entity['attr']) {
            throw new RuntimeException(_('Invalid entity configuration'));
        }

        $entity = array_replace($entity, $model[$entity['model']]);
        $entity['id'] = $eId;
        $entity['name'] = _($entity['name']);
        $entity['tab'] = $entity['tab'] ?: $entity['id'];
        $sort = 0;

        foreach ($entity['attr'] as $aId => $attr) {
            if (empty($attr['name']) || empty($attr['type']) || !($type = $cfg['type'][$attr['type']] ?? null)) {
                throw new RuntimeException(_('Invalid attribute configuration'));
            }

            $backend = $cfg['backend'][$attr['backend'] ?? $type['backend']];
            $frontend = $cfg['frontend'][$attr['frontend'] ?? $type['frontend']];
            $attr = array_replace(ATTR, $backend, $frontend, $type, $attr);
            $attr['id'] = $aId;
            $attr['name'] = _($attr['name']);
            $attr['entity'] = $entity['id'];

            if ($attr['col'] === false) {
                $attr['col'] = null;
            } elseif (!$attr['col']) {
                $attr['col'] = $attr['id'];
            }

            if (!is_numeric($attr['sort'])) {
                $attr['sort'] = $sort;
                $sort += 100;
            }

            $entity['attr'][$aId] = $attr;
        }

        $entity['attr'] = arr_order($entity['attr'], ['sort' => 'asc']);
        $data[$eId] = $entity;
    }

    return $data;
}

/**
 * I18n config listener
 */
function listener_cfg_i18n(array $data): array
{
    return $data + cfg('i18n.' . cfg('app', 'lang'));
}

/**
 * Layout config listener
 */
function listener_cfg_layout(array $data): array
{
    $a = 'account-' . (account_user() ? 'user' : 'guest');
    $b = 'action-' . request('action');
    $c = 'entity-' . request('entity');
    $d = request('path');
    $section = cfg('section');
    $data = array_replace_recursive($data[ALL], $data[$a] ?? [], $data[$b] ?? [], $data[$c] ?? [], $data[$d] ?? []);

    foreach ($data as $id => $§) {
        $data[$id] = array_replace_recursive($section[$§['section']], $§, ['id' => $id]);
    }

    return $data;
}

/**
 * Privilege config listener
 */
function listener_cfg_privilege(array $data): array
{
    foreach ($data as $id => $item) {
        $data[$id]['name'] = !empty($item['name']) ? _($item['name']) : '';
    }

    foreach (cfg('entity') as $eId => $entity) {
        foreach ($entity['actions'] as $act) {
            $data[$eId . '/' . $act]['name'] = $entity['name'] . ' ' . _(ucwords($act));
        }
    }

    return arr_order($data, ['sort' => 'asc', 'name' => 'asc']);
}

/**
 * Section config listener
 */
function listener_cfg_section(array $data): array
{
    foreach ($data as $id => $item) {
        $data[$id] = array_replace_recursive(SECTION, $item);
    }

    return $data;
}

/**
 * Toolbar config listener
 */
function listener_cfg_toolbar(array $data): array
{
    foreach ($data as $key => $item) {
        if (allowed_url($item['url'])) {
            $data[$key]['name'] = _($item['name']);
        } else {
            unset($data[$key]);
        }
    }

    return $data;
}

/**
 * Page pre-save listener
 */
function listener_postsave(array $data): array
{
    $file = request('file');

    foreach ($data['_entity']['attr'] as $aId => $attr) {
        if ($attr['frontend'] === 'file' && !empty($data[$aId]) && !file_upload($file[$aId]['tmp_name'], $data[$aId])) {
            throw new RuntimeException(_('File upload failed for %s', $data[$aId]));
        }
    }

    return $data;
}

/**
 * Page pre-save listener
 */
function listener_page_presave(array $data): array
{
    if ($data['name'] !== ($data['_old']['name'] ?? null)) {
        $base = filter_id($data['name']);
        $data['url'] = url($base . URL['page']);

        for ($i = 1; one('page', [['url', $data['url']]]); $i++) {
            $data['url'] = url($base . '-' . $i . URL['page']);
        }
    }

    return $data;
}
