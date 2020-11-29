<?php
declare(strict_types=1);

namespace attr\entity;

use app;
use attr;
use entity;
use DomainException;

/**
 * @throws DomainException
 */
function validator(int $val, array $attr): int
{
    if ($val && !entity\size($attr['ref'], [['id', $val]])) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

function viewer(int $val, array $attr): string
{
    return entity\one($attr['ref'], [['id', $val]], select: ['name'])['name'];
}

function opt(array $data, array $attr): array
{
    if (($opt = &app\registry('opt')['entity'][$attr['ref']]) === null) {
        if ($attr['ref'] === 'page' || app\cfg('entity', $attr['ref'])['parent_id'] === 'page') {
            $all = entity\all($attr['ref'], select: ['id', 'name', 'position'], order: ['position' => 'asc']);
            $opt = [];

            foreach ($all as $item) {
                $opt[$item['id']] = attr\viewer($item, $item['_entity']['attr']['position']) . ' ' . $item['name'];
            }
        } else {
            $opt = array_column(entity\all($attr['ref'], select: ['id', 'name'], order: ['name' => 'asc']), 'name', 'id');
        }
    }

    return $opt;
}
