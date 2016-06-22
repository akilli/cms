<?php
namespace qnd;

/**
 * Cast to appropriate php type
 *
 * @param array $attr
 * @param mixed $value
 *
 * @return mixed
 */
function cast(array $attr, $value)
{
    if ($value === null && !empty($attr['nullable']) || $value === '' && $attr['backend'] === 'json') {
        return null;
    }

    return $attr['multiple'] && is_array($value) ? array_map($attr['cast'], $value) : $attr['cast']($value);
}

/**
 * Check wheter attribute can be ignored
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function ignorable(array $attr, array $item): bool
{
    return !empty($item['_old'][$attr['id']]) && empty($item['_reset'][$attr['id']]) && in_array($attr['frontend'], ['password', 'file']);
}
