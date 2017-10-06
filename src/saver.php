<?php
declare(strict_types = 1);

namespace cms;

use RuntimeException;

/**
 * Saver
 */
function saver(array $attr, array $data): array
{
    return $attr['saver'] ? $attr['saver']($attr, $data) : $data;
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
    $file = request('file')[$attr['id']] ?? null;

    if ($data[$attr['id']] && (!$file || !file_upload($file['tmp_name'], $data[$attr['id']]))) {
        throw new RuntimeException(_('File upload failed for %s', $data[$attr['id']]));
    }

    return $data;
}
