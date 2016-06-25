<?php
namespace qnd;

use InvalidArgumentException;
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
    if (!is_file($file)) {
        throw new InvalidArgumentException(_('File %s not found', $file));
    }

    if (!file_writable($path)) {
        throw new RuntimeException(_('Path %s is not writable', $path));
    }

    if (file_exists($path)) {
        throw new RuntimeException(_('Path %s already exists', $path));
    }

    $zip = new ZipArchive();

    if (!$zip->open($file)) {
        throw new RuntimeException(_('Could not open ZIP file %s', $file));
    }

    if (!$zip->extractTo($path)) {
        throw new RuntimeException(_('Could not extract ZIP file to %s', $path));
    }

    return $zip->close();
}
