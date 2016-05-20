<?php
namespace qnd;

/**
 * Deleter
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function deleter(array $attr, array & $item): bool
{
    $callback = fqn('deleter_' . $attr['type']);

    if (is_callable($callback)) {
        return $callback($attr, $item);
    }

    // Temporary
    if ($attr['frontend'] === 'file') {
        return deleter_file($attr, $item);
    }

    return true;
}

/**
 * File deleter
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function deleter_file(array $attr, array & $item): bool
{
    if (!empty($item[$attr['id']]) && !media_delete($item[$attr['id']])) {
        $item['_error'][$attr['id']] = _('Could not delete old file %s', $item[$attr['id']]);
        return false;
    }

    return true;
}
