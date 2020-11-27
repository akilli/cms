<?php
declare(strict_types=1);

namespace viewer;

use entity;

/**
 * Entity
 */
function entity(int $val, array $attr): string
{
    return entity\one($attr['ref'], [['id', $val]], select: ['name'])['name'];
}
