<?php
declare(strict_types=1);

namespace opt;

use app;
use arr;
use attr;
use entity;

function block(): array
{
    if (($opt = &app\registry('opt')['block']) === null) {
        $cfg = app\cfg('layout')['html'];
        unset($cfg['head']);
        $ids = array_keys(arr\filter($cfg, 'type', 'container'));
        $opt = array_combine($ids, $ids);
    }

    return $opt;
}

function bool(): array
{
    return [app\i18n('No'), app\i18n('Yes')];
}

function entity(array $data, array $attr): array
{
    if (($opt = &app\registry('opt')['entity'][$attr['ref']]) === null) {
        if ($attr['ref'] === 'page' || app\cfg('entity', $attr['ref'])['parent_id'] === 'page') {
            $all = entity\all($attr['ref'], select: ['id', 'name', 'position'], order: ['position' => 'asc']);
            $opt = [];

            foreach ($all as $item) {
                $opt[$item['id']] = attr\viewer($item, $item['_entity']['attr']['position']) . ' ' . $item['name'];
            }
        } else {
            $opt = array_column(
                entity\all($attr['ref'], select: ['id', 'name'], order: ['name' => 'asc']),
                'name',
                'id'
            );
        }
    }

    return $opt;
}

function entitychild(array $data): array
{
    if ($data['_entity']['parent_id']) {
        return [$data['_entity']['id'] => $data['_entity']['name']];
    }

    if (($opt = &app\registry('opt')['entitychild'][$data['_entity']['id']]) === null) {
        $opt = array_column(arr\filter(app\cfg('entity'), 'parent_id', $data['_entity']['id']), 'name', 'id');
        asort($opt);
    }

    return $opt;
}

function privilege(): array
{
    if (($opt = &app\registry('opt')['privilege']) === null) {
        $opt = [];

        foreach (app\cfg('privilege') as $id => $privilege) {
            if (!$privilege['auto'] && !$privilege['use'] && app\allowed($id)) {
                $opt[$id] = $privilege['name'];
            }
        }

        asort($opt);
    }

    return $opt;
}
