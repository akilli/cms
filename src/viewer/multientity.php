<?php
declare(strict_types=1);

namespace viewer;

use entity;

/**
 * Multi-Entity
 */
function multientity(array $val, array $attr): string
{
    return implode(', ', array_column(entity\all($attr['ref'], [['id', $val]], select: ['name']), 'name'));
}
