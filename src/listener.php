<?php
declare(strict_types = 1);

namespace listener;

use app;
use arr;
use ent;
use file;
use http;
use DomainException;

/**
 * Entity config listener
 *
 * @throws DomainException
 */
function cfg_ent(array $data): array
{
    $cfg = app\cfg('attr');

    foreach ($data as $eId => $ent) {
        $ent = arr\replace(APP['ent'], $ent, ['id' => $eId]);
        $ent['name'] = app\i18n((string) $ent['name']);
        $p = $ent['parent'] && !empty($data[$ent['parent']]) ? $data[$ent['parent']] : null;
        $a = ['id' => null, 'name' => null];

        if (!$ent['name'] || !$ent['type'] || !$ent['parent'] && array_intersect_key($a, $ent['attr']) !== $a || $ent['parent'] && (!$p || $p['parent'])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        } elseif ($ent['parent']) {
            $ent['attr'] = $p['attr'] + $ent['attr'];
        }

        foreach ($ent['attr'] as $aId => $attr) {
            if (empty($attr['name']) || empty($attr['type']) || empty($cfg[$attr['type']]) || $attr['type'] === 'ent' && empty($attr['ent'])) {
                throw new DomainException(app\i18n('Invalid configuration'));
            }

            $attr = arr\replace(APP['attr'], $cfg[$attr['type']], $attr, ['id' => $aId, 'name' => app\i18n($attr['name'])]);

            if (!in_array($attr['backend'], APP['backend'])) {
                throw new DomainException(app\i18n('Invalid configuration'));
            }

            $ent['attr'][$aId] = $attr;
        }

        $data[$eId] = $ent;
    }

    return $data;
}

/**
 * I18n config listener
 */
function cfg_i18n(array $data): array
{
    return $data + app\load('i18n/' . locale_get_primary_language(''));
}

/**
 * Privilege config listener
 */
function cfg_priv(array $data): array
{
    foreach ($data as $id => $item) {
        $item = arr\replace(APP['priv'], $item);
        $item['name'] = $item['name'] ? app\i18n($item['name']) : '';
        $item['assignable'] = !$item['priv'] && $item['active'] && $item['assignable'];
        $data[$id] = $item;
    }

    foreach (app\cfg('ent') as $ent) {
        if (isset($ent['act']['edit']) && in_array('page', [$ent['id'], $ent['parent']])) {
            $id = $ent['id'] . '-publish';
            $data[$id]['name'] = $ent['name'] . ' ' . app\i18n(ucwords('Publish'));
            $data[$id] = arr\replace(APP['priv'], $data[$id]);
        }

        foreach (array_keys($ent['act']) as $act) {
            $id = $ent['id'] . '/' . $act;
            $data[$id]['name'] = $ent['name'] . ' ' . app\i18n(ucwords($act));
            $data[$id] = arr\replace(APP['priv'], $data[$id]);
        }
    }

    return $data;
}

/**
 * Toolbar config listener
 *
 * @throws DomainException
 */
function cfg_toolbar(array $data): array
{
    foreach ($data as $act => $item) {
        if (empty($item['name'])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }

        $data[$act] = arr\replace(APP['toolbar'], $item, ['name' => app\i18n($item['name']), 'url' => app\url($act)]);
    }

    return arr\order($data, ['sort' => 'asc']);
}

/**
 * Entity postfilter listener
 */
function ent_postfilter(array $data): array
{
    $attrs = $data['_ent']['attr'];

    foreach (array_intersect_key($data, $data['_ent']['attr']) as $aId => $val) {
        if ($attrs[$aId]['type'] === 'password' && $val && !($data[$aId] = password_hash($val, PASSWORD_DEFAULT))) {
            $data['_error'][$aId] = app\i18n('Invalid password');
        }
    }

    return $data;
}

/**
 * File entity prefilter listener
 */
function ent_prefilter_file(array $data): array
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
 * File entity postsave listener
 *
 * @throws DomainException
 */
function ent_postsave_file(array $data): array
{
    $file = http\req('file')['name'] ?? null;

    if ($file && !file\upload($file['tmp_name'], app\path('asset', $data['_ent']['id'] . '/' . $data['id'] . '.' . $data['type']))) {
        throw new DomainException(app\i18n('File upload failed for %s', $file['name']));
    }

    return $data;
}

/**
 * File entity postdelete listener
 *
 * @throws DomainException
 */
function ent_postdelete_file(array $data): array
{
    if (!file\delete(app\path('asset', $data['_ent']['id'] . '/' . $data['id'] . '.' . $data['type']))) {
        throw new DomainException(app\i18n('Could not delete %s', $data['name']));
    }

    return $data;
}

/**
 * Page entity postfilter listener
 */
function ent_postfilter_page(array $data): array
{
    $old = $data['_old'];
    $parent = !empty($data['parent']) ? ent\one('page', [['id', $data['parent']]]) : null;

    if ($old && $parent && $old['id'] && in_array($old['id'], $parent['path'])) {
        $data['_error']['parent'] = app\i18n('Cannot assign the page itself or a child page as parent');
    }

    return $data;
}
