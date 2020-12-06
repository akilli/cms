<?php
declare(strict_types=1);

namespace event\page;

use app;
use entity;

function postvalidate_menu(array $data): array
{
    if ($data['_old']
        && !empty($data['parent_id'])
        && ($parent = entity\one('page', crit: [['id', $data['parent_id']]], select: ['path']))
        && in_array($data['_old']['id'], $parent['path'])
    ) {
        $data['_error']['parent_id'][] = app\i18n('Cannot assign the page itself or a subpage as parent');
    }

    return $data;
}

function postvalidate_url(array $data): array
{
    $root = entity\one('page', crit: [['url', '/']], select: ['id']);
    $slug = $data['slug'] ?? $data['_old']['slug'] ?? null;
    $pId = array_key_exists('parent_id', $data) ? $data['parent_id'] : ($data['_old']['parent_id'] ?? null);
    $crit = [['slug', $slug], ['parent_id', [null, $root['id']]], ['id', $data['_old']['id'] ?? null, APP['op']['!=']]];

    if (($pId === null || $pId === $root['id']) && entity\size('page', $crit)) {
        $data['_error']['slug'][] = app\i18n('Please change slug to generate an unique URL');
    }

    return $data;
}

function presave(array $data): array
{
    $data['account_id'] = app\data('account', 'id');

    return $data;
}
