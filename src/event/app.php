<?php
declare(strict_types=1);

namespace event\app;

use app;
use arr;
use entity;

function data(array $data): array
{
    $data = arr\replace(APP['data']['app'], $data);
    $request = app\data('request');

    if (preg_match('#^/(?:|[a-z0-9_\-\./]+\.html)$#', $request['url'], $match)
        && ($page = entity\one('page', crit: [['url', $request['url']]], select: ['id', 'entity_id']))
        && ($data['page'] = entity\one($page['entity_id'], crit: [['id', $page['id']]]))
    ) {
        $data['entity_id'] = $data['page']['entity_id'];
        $data['action'] = 'view';
        $data['id'] = $data['page']['id'];
        $data['entity'] = $data['page']['_entity'];
    } elseif (preg_match('#^/([a-z_]+)(?:|/([^/\.]+))\.json$#u', $request['url'], $match)) {
        $data['entity_id'] = $match[1];
        $data['action'] = isset($match[2]) ? 'view' : 'index';
        $data['id'] = $match[2] ?? null;
        $data['type'] = 'json';
    } elseif (preg_match('#^/([a-z_]+)/([a-z_]+)(?:|/([^/\.]+))$#u', $request['url'], $match)) {
        $data['entity_id'] = $match[1];
        $data['action'] = $match[2];
        $data['id'] = $match[3] ?? null;
    }

    $data['entity'] = !$data['entity'] && $data['entity_id'] ? app\cfg('entity', $data['entity_id']) : $data['entity'];
    $data['parent_id'] = $data['entity']['parent_id'] ?? null;
    $public = empty(app\cfg('privilege', app\id($data['entity_id'], $data['action']))['active']);
    $data['area'] = $public ? '_public_' : '_admin_';
    $data['invalid'] = !$data['entity_id']
        || !$data['action']
        || !app\allowed(app\id($data['entity_id'], $data['action']))
        || $data['entity'] && !in_array($data['action'], $data['entity']['action'])
        || !$data['page']
            && in_array($data['action'], ['delete', 'view'])
            && (!$data['id'] || $data['entity'] && !entity\size($data['entity_id'], [['id', $data['id']]]))
        || $data['page'] && $data['page']['disabled'];

    return $data;
}
