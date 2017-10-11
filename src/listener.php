<?php
declare(strict_types = 1);

namespace listener;

use const app\{ALL, URL};
use const attr\ATTR;
use const entity\ENTITY;
use const section\SECTION;
use function app\_;
use function http\req;
use account;
use arr;
use app;
use entity;
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
function cfg_entity(array $data): array
{
    $cfg = app\cfg('attr');

    foreach ($data as $eId => $entity) {
        $entity = array_replace(ENTITY, $entity);

        if (!$entity['name'] || !$entity['model'] || !$entity['attr']) {
            throw new RuntimeException(_('Invalid entity configuration'));
        }

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

        $entity['attr'] = arr\order($entity['attr'], ['sort' => 'asc']);
        $data[$eId] = $entity;
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
    $c = 'entity-' . req('entity');
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
        $data[$id]['name'] = !empty($item['name']) ? _($item['name']) : '';
    }

    foreach (app\cfg('entity') as $eId => $entity) {
        foreach ($entity['act'] as $act) {
            $data[$eId . '/' . $act]['name'] = $entity['name'] . ' ' . _(ucwords($act));
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
            $data[$key]['name'] = _($item['name']);
        } else {
            unset($data[$key]);
        }
    }

    return $data;
}

/**
 * Entity post-save listener
 */
function entity_postsave(array $data): array
{
    $file = req('file');

    foreach ($data['_entity']['attr'] as $aId => $attr) {
        if ($attr['frontend'] === 'file' && !empty($data[$aId]) && !file\upload($file[$aId]['tmp_name'], $data[$aId])) {
            throw new RuntimeException(_('File upload failed for %s', $data[$aId]));
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

        for ($i = 1; entity\one('page', [['url', $data['url']]]); $i++) {
            $data['url'] = app\url($base . '-' . $i . URL['page']);
        }
    }

    return $data;
}
