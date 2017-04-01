<?php
declare(strict_types = 1);

namespace qnd;

use RuntimeException;

/**
 * Export project
 *
 * @param int $id
 *
 * @return string
 *
 * @throws RuntimeException
 */
function export(int $id): string
{
    if (!$project = one('project', [['id', $id]])) {
        throw new RuntimeException(_('Invalid project ID %d', (string) $id));
    }

    return '';
}
