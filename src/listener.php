<?php
declare(strict_types = 1);

namespace listener;

use app;
use arr;
use ent;
use file;
use filter;
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
 * Entity post-save listener
 *
 * @throws RuntimeException
 */
function ent_postsave(array $data): array
{
    $file = http\req('file');

    foreach ($data['_ent']['attr'] as $aId => $attr) {
        if ($attr['frontend'] !== 'file' || empty($data[$aId])) {
            continue;
        }

        if (is_file(app\path('data', $data[$aId])) && ($data['_old'][$aId] ?? null) !== $data[$aId]) {
            throw new RuntimeException(app\i18n('File %s already exists', $data[$aId]));
        }

        if (!file\upload($file[$aId]['tmp_name'], $data[$aId])) {
            throw new RuntimeException(app\i18n('File upload failed for %s', $data[$aId]));
        }
    }

    return $data;
}

/**
 * Page post-validate listener
 */
function page_postvalidate(array $data): array
{
    $oldId = $data['_old']['id'] ?? null;

    if ($data['parent_id'] && $oldId && in_array($oldId, ent\one('page', [['id', $data['parent_id']]])['path'])) {
        $data['_error']['parent_id'] = app\i18n('Cannot assign the page itself or a child page as parent');
    }

    return $data;
}

/**
 * Page pre-save listener
 */
function page_presave(array $data): array
{
    if ($data['name'] !== ($data['_old']['name'] ?? null)) {
        $base = filter\id($data['name']);
        $data['url'] = app\url($base . APP['url.ext']);

        for ($i = 1; ent\one('page', [['url', $data['url']]]); $i++) {
            $data['url'] = app\url($base . '-' . $i . APP['url.ext']);
        }
    }

    return $data;
}
