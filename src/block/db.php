<?php
declare(strict_types=1);

namespace block\db;

use entity;
use layout;

function render(array $block): string
{
    if ($block['cfg']['entity_id']
        && $block['cfg']['id']
        && ($data = entity\one($block['cfg']['entity_id'], [['id', $block['cfg']['id']]]))
    ) {
        return layout\render(layout\db_cfg($data, ['id' => $block['id']]));
    }

    return '';
}
