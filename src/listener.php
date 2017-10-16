<?php
declare(strict_types = 1);

namespace listener;

use const account\PRIV;
use const app\URL;
use const attr\ATTR;
use const ent\ENT;
use const layout\SECTION;
use account;
use app;
use arr;
use ent;
use file;
use filter;
use http;
use RuntimeException;

/**
 * App config listener
 */
function cfg_app(array $data): array
{
    ini_set('default_charset', $data['charset']);
    ini_set('intl.default_locale', $data['locale']);
    ini_set('date.timezone', $data['timezone']);

    return $data;
}

/**
 * Entity config listener
 */
function cfg_ent(array $data): array
{
    $cfg = app\cfg('attr');

    foreach ($data as $eId => $ent) {
        $ent = array_replace(ENT, $ent);

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
            $attr = array_replace(ATTR, $backend, $frontend, $type, $attr);
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
    return $data + app\cfg('i18n.' . app\cfg('app', 'lang'));
}

/**
 * Layout config listener
 */
function cfg_layout(array $data): array
{
    $a = 'account-' . (account\user() ? 'user' : 'guest');
    $code = http_response_code();

    if ($code === 403) {
        $b = 'act-denied';
        $c = 'ent-app';
        $d = 'app/denied';
    } elseif ($code === 404) {
        $b = 'act-error';
        $c = 'ent-app';
        $d = 'app/error';
    } else {
        $b = 'act-' . http\req('act');
        $c = 'ent-' . http\req('ent');
        $d = http\req('path');
    }

    $data = array_replace_recursive($data['_all_'], $data[$a] ?? [], $data[$b] ?? [], $data[$c] ?? [], $data[$d] ?? []);

    foreach ($data as $id => $§) {
        $data[$id] = array_replace_recursive(SECTION, $§, ['id' => $id]);
    }

    return $data;
}

/**
 * Privilege config listener
 */
function cfg_priv(array $data): array
{
    foreach ($data as $id => $item) {
        $data[$id]['name'] = !empty($item['name']) ? app\i18n($item['name']) : '';
        $data[$id] = array_replace(PRIV, $data[$id]);
    }

    foreach (app\cfg('ent') as $eId => $ent) {
        foreach (array_keys($ent['act']) as $act) {
            $id = $eId . '/' . $act;
            $data[$id]['name'] = $ent['name'] . ' ' . app\i18n(ucwords($act));
            $data[$id] = array_replace(PRIV, $data[$id]);
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
        if (account\allowed_url($item['url'])) {
            $data[$key]['name'] = app\i18n($item['name']);
        } else {
            unset($data[$key]);
        }
    }

    return $data;
}

/**
 * Entity post-save listener
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
 * Page pre-save listener
 */
function page_presave(array $data): array
{
    $oldId = $data['_old']['id'] ?? null;
    $oldName = $data['_old']['name'] ?? null;

    if ($data['parent_id'] && $oldId && in_array($oldId, ent\one('page', [['id', $data['parent_id']]])['path'])) {
        throw new RuntimeException(app\i18n('Cannot assign the page itself or a child page as parent'));
    }

    if ($data['name'] !== $oldName) {
        $base = filter\id($data['name']);
        $data['url'] = app\url($base . URL['page']);

        for ($i = 1; ent\one('page', [['url', $data['url']]]); $i++) {
            $data['url'] = app\url($base . '-' . $i . URL['page']);
        }
    }

    return $data;
}
