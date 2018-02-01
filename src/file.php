<?php
declare(strict_types = 1);

namespace file;

use app;

/**
 * Uploads a file
 */
function upload(string $src, string $dest): bool
{
    return $src && $dest && dir(dirname($dest)) && move_uploaded_file($src, $dest);
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

    foreach (array_diff(scandir($path), ['.', '..']) as $id) {
        $file = $path . '/' . $id;

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
function dir(string $path): bool
{
    return writable($path) && (is_dir($path) || mkdir($path, 0755, true));
}

/**
 * Checks if path is writable
 */
function writable(string $path): bool
{
    return strpos($path, app\path('asset')) === 0;
}
