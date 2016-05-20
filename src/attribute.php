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
    if ($value === null && !empty($attr['nullable'])) {
        return null;
    }

    if ($attr['backend'] === 'bool') {
        return boolval($value);
    }

    if ($attr['backend'] === 'int') {
        return intval($value);
    }

    if ($attr['backend'] === 'decimal') {
        return floatval($value);
    }

    if ($attr['multiple'] && is_array($value)) {
        foreach ($value as $k => $v) {
            $value[$k] = cast($attr, $v);
        }

        return $value;
    }

    return strval($value);
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
    $mustEdit = empty($item[$attr['id']]) || $attr['context'] === 'edit' && !empty($item[$attr['id']]);

    return !empty($item['_old'])
        && empty($item['_reset'][$attr['id']])
        && $mustEdit
        && in_array($attr['frontend'], ['password', 'file']);
}
