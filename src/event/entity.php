<?php
declare(strict_types=1);

namespace event;

use app;
use arr;
use entity;
use file;
use DomainException;

/**
 * Entity postvalidate password
 */
function entity_postvalidate_password(array $data): array
{
    foreach (array_intersect_key($data, $data['_entity']['attr']) as $attrId => $val) {
        if ($data['_entity']['attr'][$attrId]['type'] === 'password'
            && $val
            && !($data[$attrId] = password_hash($val, PASSWORD_DEFAULT))
        ) {
            $data['_error'][$attrId][] = app\i18n('Invalid password');
        }
    }

    return $data;
}

/**
 * Entity postvalidate unique
 */
function entity_postvalidate_unique(array $data): array
{
    foreach ($data['_entity']['unique'] as $attrIds) {
        $item = arr\replace(array_fill_keys($attrIds, null), $data['_old'], $data);
        $crit = [['id', $data['_old']['id'] ?? null, APP['op']['!=']]];
        $labels = [];

        foreach ($attrIds as $attrId) {
            $crit[] = [$attrId, $item[$attrId]];
            $labels[] = $data['_entity']['attr'][$attrId]['name'];
        }

        if (entity\size($data['_entity']['id'], $crit)) {
            foreach ($attrIds as $attrId) {
                $data['_error'][$attrId][] = app\i18n('Combination of %s must be unique', implode(', ', $labels));
            }
        }
    }

    return $data;
}

/**
 * File entity prevalidate
 */
function entity_prevalidate_file(array $data): array
{
    if ($data['_entity']['id'] === 'file_iframe') {
        $data['mime'] = 'text/html';

        if ($data['_old'] && !empty($data['url']) && $data['url'] !== $data['_old']['url']) {
            $data['_error']['url'][] = app\i18n('URL must not change');
        }
    } elseif ($data['_entity']['attr']['url']['uploadable'] && !empty($data['url'])) {
        if (!$item = app\data('request', 'file')['url'] ?? null) {
            $data['_error']['url'][] = app\i18n('No upload file');
        } elseif ($data['_old'] && $item['type'] !== $data['_old']['mime']) {
            $data['_error']['url'][] = app\i18n('MIME-Type must not change');
        } elseif ($data['_old']) {
            $data['url'] = $data['_old']['url'];
        } else {
            $data['url'] = app\file($item['name']);
            $data['mime'] = $item['type'];

            if (entity\size('file', [[['url', $data['url']], ['thumb', $data['url']]]])) {
                $data['_error']['url'][] = app\i18n('Please change filename to generate an unique URL');
            }
        }
    }

    if (!empty($data['thumb']) && ($item = app\data('request', 'file')['thumb'] ?? null)) {
        $data['thumb'] = app\file($item['name']);
        $crit = array_merge(
            [[['url', $data['thumb']], ['thumb', $data['thumb']]]],
            $data['_old'] ? [['id', $data['_old']['id'], APP['op']['!=']]] : []
        );

        if (entity\size('file', $crit)) {
            $data['_error']['thumb'][] = app\i18n('Please change filename to generate an unique URL');
        }
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
    $upload = function (string $attrId) use ($data): ?string {
        $item = app\data('request', 'file')[$attrId] ?? null;
        return $item && !file\upload($item['tmp_name'], app\filepath($data[$attrId])) ? $item['name'] : null;
    };

    if ($data['_entity']['attr']['url']['uploadable'] && !empty($data['url']) && ($name = $upload('url'))
        || !empty($data['thumb']) && ($name = $upload('thumb'))
    ) {
        throw new DomainException(app\i18n('Could not upload %s', $name));
    }

    if (array_key_exists('thumb', $data)
        && !$data['thumb']
        && $data['_old']['thumb']
        && !file\delete(app\filepath($data['_old']['thumb']))
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
    if ($data['_entity']['attr']['url']['uploadable'] && !file\delete(app\filepath($data['_old']['url']))
        || $data['_old']['thumb'] && !file\delete(app\filepath($data['_old']['thumb']))
    ) {
        throw new DomainException(app\i18n('Could not delete file'));
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
        && ($parent = entity\one('page', [['id', $data['parent_id']]], select: ['path']))
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
    $root = entity\one('page', [['url', '/']], select: ['id']);
    $slug = $data['slug'] ?? $data['_old']['slug'] ?? null;
    $pId = array_key_exists('parent_id', $data) ? $data['parent_id'] : ($data['_old']['parent_id'] ?? null);
    $crit = [['slug', $slug], ['parent_id', [null, $root['id']]], ['id', $data['_old']['id'] ?? null, APP['op']['!=']]];

    if (($pId === null || $pId === $root['id']) && entity\size('page', $crit)) {
        $data['_error']['slug'][] = app\i18n('Please change slug to generate an unique URL');
    }

    return $data;
}

/**
 * Page entity presave
 */
function entity_presave_page(array $data): array
{
    $data['account_id'] = app\data('account', 'id');

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
