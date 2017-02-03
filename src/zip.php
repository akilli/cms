<?php
declare(strict_types=1);

namespace qnd;

use RuntimeException;
use ZipArchive;

/**
 * Unzip file
 *
 * @param string $file
 * @param string $path
 *
 * @return bool
 *
 * @throws RuntimeException
 */
function unzip(string $file, string $path): bool
{
    $zip = new ZipArchive();

    if (!$zip->open($file)) {
        throw new RuntimeException(_('Could not open ZIP file %s', $file));
    }

    if (!$zip->extractTo($path)) {
        throw new RuntimeException(_('Could not extract ZIP file to %s', $path));
    }

    return $zip->close();
}
