<?php
declare(strict_types = 1);

namespace listener;

use const app\{ALL, URL};
use const attr\ATTR;
use const ent\ENT;
use const section\SECTION;
use function app\i18n;
use function http\req;
use account;
use arr;
use app;
use ent;
use file;
use filter;
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

        if (!$ent['name'] || !$ent['model'] || !$ent['attr']) {
            throw new RuntimeException(i18n('Invalid entity configuration'));
        }

        $ent['id'] = $eId;
        $ent['name'] = i18n($ent['name']);
        $ent['tab'] = $ent['tab'] ?: $ent['id'];
        $sort = 0;

        foreach ($ent['attr'] as $aId => $attr) {
            if (empty($attr['name']) || empty($attr['type']) || !($type = $cfg['type'][$attr['type']] ?? null)) {
                throw new RuntimeException(i18n('Invalid attribute configuration'));
            }

            $backend = $cfg['backend'][$attr['backend'] ?? $type['backend']];
            $frontend = $cfg['frontend'][$attr['frontend'] ?? $type['frontend']];
            $attr = array_replace(ATTR, $backend, $frontend, $type, $attr);
            $attr['id'] = $aId;
            $attr['name'] = i18n($attr['name']);

            if ($attr['col'] === false) {
                $attr['col'] = null;
            } elseif (!$attr['col']) {
                $attr['col'] = $attr['id'];
            }

            if (!is_numeric($attr['sort'])) {
                $attr['sort'] = $sort;
                $sort += 100;
            }

            $ent['attr'][$aId] = $attr;
        }

        $ent['attr'] = arr\order($ent['attr'], ['sort' => 'asc']);
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
    $b = 'act-' . req('act');
    $c = 'ent-' . req('ent');
    $d = req('path');
    $data = array_replace_recursive($data[ALL], $data[$a] ?? [], $data[$b] ?? [], $data[$c] ?? [], $data[$d] ?? []);

    foreach ($data as $id => $§) {
        $data[$id] = array_replace_recursive(SECTION, $§, ['id' => $id]);
    }

    return $data;
}

/**
 * Privilege config listener
 */
function cfg_privilege(array $data): array
{
    foreach ($data as $id => $item) {
        $data[$id]['name'] = !empty($item['name']) ? i18n($item['name']) : '';
    }

    foreach (app\cfg('ent') as $eId => $ent) {
        foreach ($ent['act'] as $act) {
            $data[$eId . '/' . $act]['name'] = $ent['name'] . ' ' . i18n(ucwords($act));
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
            $data[$key]['name'] = i18n($item['name']);
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
    $file = req('file');

    foreach ($data['_ent']['attr'] as $aId => $attr) {
        if ($attr['frontend'] === 'file' && !empty($data[$aId]) && !file\upload($file[$aId]['tmp_name'], $data[$aId])) {
            throw new RuntimeException(i18n('File upload failed for %s', $data[$aId]));
        }
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
        $data['url'] = app\url($base . URL['page']);

        for ($i = 1; ent\one('page', [['url', $data['url']]]); $i++) {
            $data['url'] = app\url($base . '-' . $i . URL['page']);
        }
    }

    return $data;
}
