<?php
declare(strict_types=1);

namespace opt;

use app;
use attr;
use entity;

/**
 * Entity
 */
function entity(array $data, array $attr): array
{
    if (($opt = &app\registry('opt')['entity'][$attr['ref']]) === null) {
        if ($attr['ref'] === 'page_content') {
            $all = entity\all($attr['ref'], select: ['id', 'name', 'pos'], order: ['pos' => 'asc']);
            $opt = [];

            foreach ($all as $item) {
                $opt[$item['id']] = attr\viewer($item, $item['_entity']['attr']['pos']) . ' ' . $item['name'];
            }
        } else {
            $opt = array_column(entity\all($attr['ref'], select: ['id', 'name'], order: ['name' => 'asc']), 'name', 'id');
        }
    }

    return $opt;
}
