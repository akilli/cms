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
    if (!empty($item[$attr['uid']]) && !file_delete_media($item[$attr['uid']])) {
        $item['_error'][$attr['uid']] = _('Could not delete old file %s', $item[$attr['uid']]);
        return false;
    }

    return true;
}
