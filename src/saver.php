<?php
declare(strict_types = 1);

namespace qnd;

use RuntimeException;

/**
 * Password saver
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 */
function saver_password(array $attr, array $data): array
{
    if ($data[$attr['id']]) {
        $data[$attr['id']] = password_hash($data[$attr['id']], PASSWORD_DEFAULT);
    }

    return $data;
}

/**
 * File saver
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws RuntimeException
 */
function saver_file(array $attr, array $data): array
{
    $file = request('files')[$attr['id']] ?? null;

    if ($data[$attr['id']] && (!$file || !file_upload($file['tmp_name'], $data[$attr['id']]))) {
        throw new RuntimeException(_('File upload failed for %s', $data[$attr['id']]));
    }

    return $data;
}
