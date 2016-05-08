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

    if (!empty($attr['multiple']) && is_array($value)) {
        foreach ($value as $k => $v) {
            $value[$k] = cast($attr, $v);
        }

        return $value;
    }

    return strval($value);
}

/**
 * Retrieve value
 *
 * @param array $attr
 * @param array $item
 *
 * @return mixed
 */
function value(array $attr, array $item)
{
    return $item[$attr['id']] ?? $attr['default'] ?? null;
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
    $mustEdit = empty($item[$attr['id']]) || $attr['action'] === 'edit' && !empty($item[$attr['id']]);

    return !empty($item['_old'])
        && empty($item['_reset'][$attr['id']])
        && $mustEdit
        && in_array($attr['frontend'], ['password', 'file']);
}

/**
 * Prepare attribute if edit action is allowed
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function editable(array & $attr, array $item): bool
{
    if (!meta_action('edit', $attr)) {
        return false;
    }

    if (!empty($item['_error'][$attr['id']])) {
        $attr['class'][] = 'invalid';
    }

    $attr['action'] = 'edit';

    return true;
}

/**
 * Prepare attribute if view action is allowed
 *
 * @param array $attr
 *
 * @return bool
 */
function viewable(array & $attr): bool
{
    return $attr['action'] && meta_action($attr['action'], $attr);
}
