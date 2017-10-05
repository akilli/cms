<?php
declare(strict_types = 1);

namespace cms;

/**
 * Constants
 */
const ATTR = [
    'id' => null,
    'name' => null,
    'col' => null,
    'auto' => false,
    'sort' => 0,
    'type' => null,
    'backend' => null,
    'frontend' => null,
    'db_type' => null,
    'pdo' => null,
    'nullable' => false,
    'required' => false,
    'uniq' => false,
    'multiple' => false,
    'searchable' => false,
    'opt' => [],
    'actions' => [],
    'val' => null,
    'min' => 0,
    'max' => 0,
    'minlength' => 0,
    'maxlength' => 0,
    'entity' => null,
    'html' => [],
    'validator' => null,
    'saver' => null,
    'loader' => null,
    'editor' => null,
    'viewer' => null,
];
const DATE = ['b' => 'Y-m-d', 'f' => 'Y-m-d'];
const DATETIME = ['b' => 'Y-m-d H:i:s', 'f' => 'Y-m-d\TH:i'];
const TIME = ['b' => 'H:i:s', 'f' => 'H:i'];

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
