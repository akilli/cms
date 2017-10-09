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
