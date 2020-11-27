<?php
declare(strict_types=1);

namespace block;

use entity;
use layout;

/**
 * Database
 */
function db(array $block): string
{
    if ($block['cfg']['entity_id']
        && $block['cfg']['id']
        && ($data = entity\one($block['cfg']['entity_id'], [['id', $block['cfg']['id']]]))
    ) {
        return layout\render(layout\db($data, ['id' => $block['id']]));
    }

    return '';
}
