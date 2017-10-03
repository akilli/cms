<?php
declare(strict_types = 1);

namespace cms;

/**
 * Cast to appropriate php type
 *
 * @param array $attr
 * @param mixed $val
 *
 * @return mixed
 */
function cast(array $attr, $val)
{
    if ($attr['nullable'] && ($val === null || $val === '')) {
        return null;
    }

    if ($attr['multiple'] && is_array($val)) {
        foreach ($val as $k => $v) {
            $val[$k] = cast($attr, $v);
        }

        return $val;
    }

    if ($attr['backend'] === 'bool') {
        return (bool) $val;
    }

    if ($attr['backend'] === 'int') {
        return (int) $val;
    }

    if ($attr['backend'] === 'decimal') {
        return (float) $val;
    }

    return (string) $val;
}

/**
 * Check wheter attribute can be ignored
 *
 * @param array $attr
 * @param array $data
 *
 * @return bool
 */
function ignorable(array $attr, array $data): bool
{
    return !empty($data['_old'][$attr['id']]) && in_array($attr['frontend'], ['file', 'password']);
}
