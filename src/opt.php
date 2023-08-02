<?php
declare(strict_types=1);

namespace opt;

use app;
use attr;
use entity;

function bool(): array
{
    return [app\i18n('No'), app\i18n('Yes')];
}

function entity(array $data, array $attr): array
{
    if (($opt = &app\registry('opt.entity')[$attr['ref']]) === null) {
        $entity = $data['_entity']['id'] === $attr['ref'] ? $data['_entity'] : app\cfg('entity', $attr['ref']);

        if ($entity['id'] === 'menu') {
            $all = entity\all($entity['id'], select: ['id', 'name', 'position'], order: ['position' => 'asc']);
            $opt = [];

            foreach ($all as $item) {
                $opt[$item['id']] = attr\viewer($item, $item['_entity']['attr']['position']) . ' ' . $item['name'];
            }
        } elseif (!empty($entity['attr']['name'])) {
            $opt = array_column(
                entity\all($entity['id'], select: ['id', 'name'], order: ['name' => 'asc']),
                'name',
                'id'
            );
        } else {
            $opt = array_column(entity\all($entity['id'], select: ['id'], order: ['id' => 'asc']), 'id', 'id');
        }
    }

    return $opt;
}

function privilege(): array
{
    if (($opt = &app\registry('opt.privilege')) === null) {
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
