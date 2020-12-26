<?php
declare(strict_types=1);

namespace event\app;

use app;
use arr;
use entity;

function data(array $data): array
{
    $data = arr\replace(APP['data']['app'], $data);
    $url = app\data('request', 'url');

    if (preg_match('#^/(?:|[a-z0-9_\-\./]+\.html)$#', $url, $match)
        && ($page = entity\one('page', crit: [['url', $url]], select: ['id', 'entity_id']))
        && ($data['page'] = entity\one($page['entity_id'], crit: [['id', $page['id']]]))
    ) {
        $data['type'] = 'html';
        $data['entity_id'] = $data['page']['entity_id'];
        $data['action'] = 'view';
        $data['id'] = $data['page']['id'];
        $data['entity'] = $data['page']['_entity'];
    } elseif (preg_match('#^/([a-z_]+)(?:|/([^/\.]+))\.json$#u', $url, $match)) {
        $data['type'] = 'json';
        $data['entity_id'] = $match[1];
        $data['action'] = isset($match[2]) ? 'view' : 'index';
        $data['id'] = $match[2] ?? null;
    } elseif (preg_match('#^/([a-z_]+)/([a-z_]+)(?:|/([^/\.]+))$#u', $url, $match)) {
        $data['type'] = 'html';
        $data['entity_id'] = $match[1];
        $data['action'] = $match[2];
        $data['id'] = $match[3] ?? null;
    }

    $data['entity'] = !$data['entity'] && $data['entity_id'] ? app\cfg('entity', $data['entity_id']) : $data['entity'];
    $data['parent_id'] = $data['entity']['parent_id'] ?? null;
    $privilege = app\cfg('privilege', app\id($data['entity_id'], $data['action']));
    $data['area'] = $privilege && $privilege['use'] === '_public_' ? '_public_' : '_admin_';
    $data['valid'] = $data['entity_id']
        && $data['action']
        && app\allowed(app\id($data['entity_id'], $data['action']))
        && $data['entity']
        && in_array($data['action'], $data['entity']['action'])
        && (
            !in_array($data['action'], ['delete', 'view'])
            || $data['page'] && !$data['page']['disabled']
            || $data['id'] && entity\size($data['entity_id'], [['id', $data['id']]])
        );

    if ($data['valid']) {
        $data['event'] = [
            $data['type'],
            app\id($data['type'], $data['area']),
            app\id($data['type'], $data['action']),
            ...($data['parent_id'] ? [app\id($data['type'], $data['parent_id'], $data['action'])] : []),
            app\id($data['type'], $data['entity_id'], $data['action']),
            ...($data['page'] ? [app\id($data['type'], 'page', $data['action'], $data['id'])] : []),
        ];
    } else {
        $data['event'] = [$data['type'], app\id($data['type'], '_invalid_')];
    }

    return $data;
}
