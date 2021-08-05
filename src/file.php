<?php
declare(strict_types=1);

namespace file;

use app;
use DomainException;

/**
 * Lists files and directories inside the specified path
 *
 * @throws DomainException
 */
function scan(string $path): array
{
    $files = scandir($path) ?: throw new DomainException(app\i18n('Invalid path'));

    return array_diff($files, ['.', '..']);
}

/**
 * Uploads a file
 */
function upload(string $src, string $dest): bool
{
    return $src && $dest && mkdirp(dirname($dest)) && move_uploaded_file($src, $dest);
}

/**
 * Removes a file or directory
 */
function delete(string $path): bool
{
    if (!writable($path)) {
        return false;
    }

    if (!file_exists($path) || is_file($path) && unlink($path)) {
        return true;
    }

    foreach (scan($path) as $name) {
        $file = $path . '/' . $name;

        if (is_file($file)) {
            unlink($file);
        } elseif (is_dir($file)) {
            delete($file);
        }
    }

    return rmdir($path);
}

/**
 * Makes a directory if it doesn't exist
 */
function mkdirp(string $path): bool
{
    return writable($path) && (is_dir($path) || mkdir($path, 0755, true));
}

/**
 * Checks if path is writable
 */
function writable(string $path): bool
{
    return (bool) preg_match('#^(' . APP['path']['asset'] . '|' . APP['path']['tmp'] . ')($|/.+)#', $path);
}
