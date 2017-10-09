<?php
declare(strict_types = 1);

namespace cms;

use RuntimeException;

/**
 * Saver
 */
function saver(array $attr, array $data): array
{
    return $attr['nullable'] && $data[$attr['id']] === null || !$attr['saver'] ? $data : $attr['saver']($attr, $data);
}

/**
 * Password saver
 */
function saver_password(array $attr, array $data): array
{
    if ($data[$attr['id']]) {
        $data[$attr['id']] = password_hash($data[$attr['id']], PASSWORD_DEFAULT);
    } elseif (!empty($data['_old'][$attr['id']])) {
        $data[$attr['id']] = $data['_old'][$attr['id']];
    }

    return $data;
}

/**
 * File saver
 *
 * @throws RuntimeException
 */
function saver_file(array $attr, array $data): array
{
    if ($data[$attr['id']] && !file_upload(request('file')[$attr['id']]['tmp_name'], $data[$attr['id']])) {
        throw new RuntimeException(_('File upload failed for %s', $data[$attr['id']]));
    }

    return $data;
}

/**
 * JSON saver
 *
 * @throws RuntimeException
 */
function saver_json(array $attr, array $data): array
{
    if (is_array($data[$attr['id']]) && ($data[$attr['id']] = json_encode($data[$attr['id']])) === false) {
        throw new RuntimeException(_('JSON encoding failed for %s', $attr['id']));
    }

    return $data;
}
