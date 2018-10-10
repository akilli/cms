<?php
declare(strict_types = 1);

namespace event;

use app;
use arr;
use attr;
use entity;
use file;
use request;
use smtp;
use DomainException;

/**
 * Block config
 *
 * @throws DomainException
 */
function cfg_block(array $data): array
{
    foreach ($data as $val) {
        if (empty($val['call']) || !is_callable($val['call'])) {
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

    foreach ($data as $eId => $entity) {
        $entity = arr\replace(APP['entity'], $entity, ['id' => $eId]);
        $entity['name'] = app\i18n((string) $entity['name']);
        $p = $entity['parent'] && !empty($data[$entity['parent']]) ? $data[$entity['parent']] : null;
        $a = ['id' => null, 'name' => null];

        if (!$entity['name'] || !$entity['type'] || !$entity['parent'] && array_intersect_key($a, $entity['attr']) !== $a || $entity['parent'] && (!$p || $p['parent'])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        } elseif ($entity['parent']) {
            $entity['attr'] = array_replace_recursive($p['attr'], $entity['attr']);
        }

        foreach ($entity['attr'] as $aId => $attr) {
            if (empty($attr['name']) || empty($attr['type']) || empty($cfg[$attr['type']]) || $attr['type'] === 'entity' && empty($attr['ref'])) {
                throw new DomainException(app\i18n('Invalid configuration'));
            }

            if (!empty($attr['html']) && !empty($cfg[$attr['type']]['html'])) {
                $attr['html'] = array_replace($attr['html'], $cfg[$attr['type']]['html']);
            }

            $attr = arr\replace(APP['attr'], $cfg[$attr['type']], $attr, ['id' => $aId, 'name' => app\i18n($attr['name'])]);

            if (!in_array($attr['backend'], APP['backend'])) {
                throw new DomainException(app\i18n('Invalid configuration'));
            }

            $entity['attr'][$aId] = $attr;
        }

        $data[$eId] = $entity;
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
        if (array_intersect(['create', 'edit'], $entity['action']) && in_array('page', [$entity['id'], $entity['parent']])) {
            $id = $entity['id'] . '-publish';
            $data[$id]['name'] = $entity['name'] . ' ' . app\i18n(ucwords('Publish'));
            $data[$id] = arr\replace(APP['priv'], $data[$id]);
        }

        foreach ($entity['action'] as $action) {
            $id = $entity['id'] . '/' . $action;
            $data[$id]['name'] = $entity['name'] . ' ' . app\i18n(ucwords($action));
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
        if (empty($item['name']) || !empty($item['parent']) && empty($data[$item['parent']])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }

        $item = arr\replace(APP['toolbar'], $item);
        $item['name'] = app\i18n($item['name']);
        $item['level'] = 1;
        $item['sort'] = str_pad((string) $item['sort'], 5, '0', STR_PAD_LEFT) . '-' . $id;

        if ($item['parent']) {
            $item['level'] = $data[$item['parent']]['level'] + 1;
            $item['sort'] = $data[$item['parent']]['sort'] . '/' . $item['sort'];
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
    $keys = ['_all_', app\get('area')];

    if (app\get('error')) {
        $keys[] = '_error_';
    } else {
        $eId = app\get('entity');
        $action = app\get('action');
        $keys[] = $action;

        if ($parent = app\get('parent')) {
            $keys[] = $parent . '/' . $action;
        }

        $keys[] = $eId . '/' . $action;

        if ($layout = app\get('layout')) {
            $keys[] = 'page-' . $layout;
        }

        if ($id = app\get('id')) {
            $keys[] = $eId . '/' . $action . '/' . $id;
        }
    }

    foreach ($keys as $key) {
        if (!empty($cfg[$key])) {
            foreach ($cfg[$key] as $id => $§) {
                $data[$id] = empty($data[$id]) ? $§ : app\load_block($data[$id], $§);
            }
        }
    }

    foreach ($data as $id => $§) {
        if (empty($§['type']) || empty($type[$§['type']])) {
            throw new DomainException(app\i18n('Invalid block %s', $id));
        }

        $data[$id] = arr\replace(APP['block'], $type[$§['type']], $§, ['id' => $id]);
    }

    return $data;
}

/**
 * Entity postfilter
 */
function entity_postfilter(array $data): array
{
    $attrs = $data['_entity']['attr'];

    foreach (array_intersect_key($data, $data['_entity']['attr']) as $aId => $val) {
        if ($attrs[$aId]['type'] === 'password' && $val && !($data[$aId] = password_hash($val, PASSWORD_DEFAULT))) {
            $data['_error'][$aId] = app\i18n('Invalid password');
        }
    }

    return $data;
}

/**
 * File entity prefilter
 */
function entity_prefilter_file(array $data): array
{
    if (!empty($data['name'])) {
        $data['type'] = pathinfo($data['name'], PATHINFO_EXTENSION);
    }

    if (!empty($data['type']) && !empty($data['_old']['type']) && $data['type'] !== $data['_old']['type']) {
        $data['_error']['name'] = app\i18n('Cannot change filetype anymore');
    }

    return $data;
}

/**
 * Entity postsave
 */
function entity_postsave(array $data): array
{
    if (!$data['_entity']['mail'] || app\get('area') !== '_public_') {
        return $data;
    }

    $file = request\get('file');
    $text = '';
    $attach = [];

    foreach ($data as $aId => $val) {
        $attr = $data['_entity']['attr'][$aId] ?? null;

        if (!$attr || $aId === 'id') {
            continue;
        } elseif ($attr['type'] !== 'file') {
            $text .= $attr['name'] . ':' . APP['crlf'] . attr\viewer($attr, $data) . APP['crlf'] .APP['crlf'];
        } elseif (!empty($file[$aId])) {
            $attach[] = ['name' => $file[$aId]['name'], 'path' => $file[$aId]['tmp_name'], 'type' => $file[$aId]['type']];
        }
    }

    $address = app\cfg('app', 'mail.address');
    $subject = app\cfg('app', 'mail.subject');
    smtp\mail($address, $address, null, $subject, $text, $attach);

    return $data;
}

/**
 * File entity postsave
 *
 * @throws DomainException
 */
function entity_postsave_file(array $data): array
{
    $item = request\get('file')['name'] ?? null;

    if ($item && !file\upload($item['tmp_name'], app\path('file', $data['id'] . '.' . $data['type']))) {
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
    if (!file\delete(app\path('file', $data['id'] . '.' . $data['type']))) {
        throw new DomainException(app\i18n('Could not delete file'));
    }

    return $data;
}

/**
 * Page entity postfilter
 */
function entity_postfilter_page(array $data): array
{
    if (empty($data['parent_id'])) {
        return $data;
    }

    $parent = entity\one('page', [['id', $data['parent_id']]]);

    if (!empty($data['_old']['id']) && in_array($data['_old']['id'], $parent['path'])) {
        $data['_error']['parent_id'] = app\i18n('Cannot assign the page itself or a child page as parent');
    }

    if ($parent['status'] === 'archived' && (!$data['_old'] || $data['parent_id'] !== $data['_old']['parent_id'])) {
        $data['_error']['parent_id'] = app\i18n('Cannot assign archived page as parent');
    } elseif (in_array($parent['status'], ['draft', 'pending']) && !empty($data['status']) && $data['status'] !== 'draft') {
        $data['_error']['status'] = app\i18n('Status must be draft, because parent was not published yet');
    }

    return $data;
}
