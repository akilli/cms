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
    return !$attr['deleter'] || ($call = fqn('deleter_' . $attr['deleter'])) && $call($attr, $item);
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
    if (!empty($item[$attr['id']]) && !file_delete_media($item[$attr['id']])) {
        $item['_error'][$attr['id']] = _('Could not delete old file %s', $item[$attr['id']]);
        return false;
    }

    return true;
}
