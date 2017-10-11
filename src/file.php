<?php
declare(strict_types = 1);

namespace file;

use app;

/**
 * Load data from file
 */
function load(string $file): array
{
    return is_readable($file) && ($data = include $file) && is_array($data) ? $data : [];
}

/**
 * Uploads a file
 */
function upload(string $src, string $dest): bool
{
    $dest = strpos($dest, '/') === 0 ? $dest : app\path('data', $dest);

    return dir(dirname($dest)) && move_uploaded_file($src, $dest);
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
 * Checks whether specified path is writable
 */
function writable(string $path): bool
{
    return (bool) preg_match('#^(file://)?(' . app\path('data') . ')#', $path);
}

/**
 * Returns list of accepted extensions for given file type
 */
function accept(string $type): string
{
    $accept = '';

    foreach (app\cfg('file') as $ext => $types) {
        if (in_array($type, $types)) {
            $accept .= ($accept ? ', .' : '.') . $ext;
        }
    }

    return $accept;
}
