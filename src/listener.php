<?php
declare(strict_types = 1);

namespace listener;

use app;
use arr;
use ent;
use file;
use http;
use RuntimeException;

/**
 * Entity config listener
 *
 * @throws RuntimeException
 */
function cfg_ent(array $data): array
{
    $cfg = app\cfg('attr');

    foreach ($data as $eId => $ent) {
        $ent = arr\replace(APP['ent'], $ent);

        if (!$ent['name'] || !$ent['type'] || !$ent['attr']) {
            throw new RuntimeException(app\i18n('Invalid entity configuration'));
        }

        $ent['id'] = $eId;
        $ent['name'] = app\i18n($ent['name']);
        $ent['tab'] = $ent['tab'] ?: $ent['id'];

        foreach ($ent['attr'] as $aId => $attr) {
            if (empty($attr['name']) || empty($attr['type']) || !($type = $cfg['type'][$attr['type']] ?? null)) {
                throw new RuntimeException(app\i18n('Invalid attribute configuration'));
            }

            $backend = $cfg['backend'][$attr['backend'] ?? $type['backend']];
            $frontend = $cfg['frontend'][$attr['frontend'] ?? $type['frontend']];
            $attr = arr\replace(APP['attr'], $backend, $frontend, $type, $attr);
            $attr['id'] = $aId;
            $attr['name'] = app\i18n($attr['name']);

            if ($attr['col'] === false) {
                $attr['col'] = null;
            } elseif (!$attr['col']) {
                $attr['col'] = $attr['id'];
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
    return $data + app\cfg('i18n.' . locale_get_primary_language(''));
}

/**
 * Layout config listener
 */
function cfg_layout(array $data): array
{
    $code = http_response_code();

    if ($code === 403) {
        $a = 'act-denied';
        $b = 'ent-app';
        $c = 'app/denied';
    } elseif ($code === 404) {
        $a = 'act-error';
        $b = 'ent-app';
        $c = 'app/error';
    } else {
        $a = 'act-' . http\req('act');
        $b = 'ent-' . http\req('ent');
        $c = http\req('path');
    }

    $data = array_replace_recursive($data[APP['all']], $data[$a] ?? [], $data[$b] ?? [], $data[$c] ?? []);

    foreach ($data as $id => $§) {
        $data[$id] = arr\replace(APP['section'], $§, ['id' => $id]);
    }

    return $data;
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

    foreach (app\cfg('ent') as $eId => $ent) {
        foreach (array_keys($ent['act']) as $act) {
            $id = $eId . '/' . $act;
            $data[$id]['name'] = $ent['name'] . ' ' . app\i18n(ucwords($act));
            $data[$id] = arr\replace(APP['priv'], $data[$id]);
        }
    }

    return arr\order($data, ['sort' => 'asc', 'name' => 'asc']);
}

/**
 * Toolbar config listener
 */
function cfg_toolbar(array $data): array
{
    foreach ($data as $key => $item) {
        if (app\allowed_url($item['url'])) {
            $data[$key]['name'] = app\i18n($item['name']);
        } else {
            unset($data[$key]);
        }
    }

    return $data;
}

/**
 * Entity prefilter listener
 */
function ent_prefilter(array $data): array
{
    foreach ($data['_ent']['attr'] as $aId => $attr) {
        if ($attr['frontend'] === 'file' && !empty($data[$aId])) {
            $data[$aId] = $data['_ent']['id'] . '/' . $data[$aId];
        }
    }

    return $data;
}

/**
 * Entity postfilter listener
 */
function ent_postfilter(array $data): array
{
    foreach ($data['_ent']['attr'] as $aId => $attr) {
        if ($attr['frontend'] === 'password' && !empty($data[$aId]) && !($data[$aId] = password_hash($data[$aId], PASSWORD_DEFAULT))) {
            $data['_error'][$aId] = app\i18n('Invalid password');
        }
    }

    return $data;
}

/**
 * Entity postsave listener
 *
 * @throws RuntimeException
 */
function ent_postsave(array $data): array
{
    $file = http\req('file');

    foreach ($data['_ent']['attr'] as $aId => $attr) {
        if ($attr['frontend'] === 'file' && !empty($data[$aId]) && !file\upload($file[$aId]['tmp_name'], app\path('data', $data[$aId]))) {
            throw new RuntimeException(app\i18n('File upload failed for %s', $data[$aId]));
        }
    }

    return $data;
}

/**
 * Entity postdelete listener
 *
 * @throws RuntimeException
 */
function ent_postdelete(array $data): array
{
    foreach ($data['_ent']['attr'] as $aId => $attr) {
        if ($attr['frontend'] === 'file' && !empty($data[$aId]) && !file\delete(app\path('data', $data[$aId]))) {
            throw new RuntimeException(app\i18n('Could not delete %s', $data[$aId]));
        }
    }

    return $data;
}

/**
 * Media presave listener
 */
function media_presave(array $data): array
{
    $file = http\req('file')['file']['tmp_name'] ?? null;

    if ($file) {
        $data['size'] = filesize($file);
    }

    return $data;
}

/**
 * Page postfilter listener
 */
function page_postfilter(array $data): array
{
    $oldId = $data['_old']['id'] ?? null;

    if (!empty($data['parent_id']) && $oldId && in_array($oldId, ent\one('page', [['id', $data['parent_id']]])['path'])) {
        $data['_error']['parent_id'] = app\i18n('Cannot assign the page itself or a child page as parent');
    }

    return $data;
}
